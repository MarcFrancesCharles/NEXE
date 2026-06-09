import { Component, inject, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Auth } from '../../core/services/auth';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators, FormsModule } from '@angular/forms';
import { ZXingScannerModule } from '@zxing/ngx-scanner';
import { BarcodeFormat } from '@zxing/library';
import { environment } from '../../../../environments/environment';

@Component({
  selector: 'app-shop',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, FormsModule, ZXingScannerModule],
  templateUrl: './shop.html',
  styleUrl: './shop.css'
})
export class Shop implements OnInit, OnDestroy {

  storageUrl = environment.storageUrl;
  ofertes: any[] = [];
  termeCerca: string = '';
  mostrantModal = false;
  carregant = true;

  get ofertesFiltrades() {
    if (!this.termeCerca.trim()) return this.ofertes;
    const term = this.termeCerca.toLowerCase().trim();
    return this.ofertes.filter(o => o.titol?.toLowerCase().includes(term));
  }

  // Variables per la Càmera QR
  mostrantCamera = false;
  codiEscanejat: string | null = null;
  formatsPermesos = [BarcodeFormat.QR_CODE];

  // Variables pel Modal d'Accions QR
  mostrantAccionsQR = false;
  qrTokenXifrat: string = '';
  importCompra: number | null = null;

  missatgeCamera: string = '';
  tipusMissatgeCamera: 'success' | 'error' | '' = '';

  ofertaEditantId: number | null = null;
  editForm: FormGroup;
  imatgeSeleccionada: File | null = null;

  // Variables pel Comerç
  comerc: any = null;
  solicitudComerc: any = null;
  categories: any[] = [];
  ofertaEliminarId: number | null = null;
  confirmDeleteVisible: boolean = false;
  mostrantModalComerc = false;
  comercForm: FormGroup;
  imatgeComercSeleccionada: File | null = null;
  imatgeComercPreview: string | null = null;

  private http = inject(HttpClient);
  private auth = inject(Auth);
  private fb = inject(FormBuilder);

  mostrantModalCrear = false;
  crearForm: FormGroup;
  loadingCrear = false;
  missatgeCrear = '';
  errorCrear = false;

  // Estadístiques
  estadistiques: any = {
    punts_donats: 0,
    ofertes_venudes: 0,
    punts_bescanviats: 0,
    historial_vendes: []
  };

  constructor() {
    this.editForm = this.fb.group({
      titol:      ['', [Validators.required]],
      descripcio: [''],
      cost_punts: [1, [Validators.required, Validators.min(1)]],
      data_fi:    ['']
    });

    this.crearForm = this.fb.group({
      titol:              ['', [Validators.required, Validators.minLength(5)]],
      descripcio:         ['', [Validators.required]],
      cost_punts:         [50, [Validators.required, Validators.min(1)]],
      tipus_durada:       ['sempre'],
      data_personalitzada:[''],
      programar:          [false],
      data_publicacio:    ['']
    });

    // Formulari del comerç amb els nous camps de contacte
    this.comercForm = this.fb.group({
      nom_comercial:  ['', [Validators.required]],
      id_categoria:   ['', [Validators.required]],
      cif:            ['', [Validators.required]],
      // Camps de contacte i descripció (tots opcionals)
      descripcio:     [''],
      telefon:        [''],
      email_contacte: ['', [Validators.email]],
      enllac_web:     [''],
      instagram:      [''],
    });
  }

  ngOnInit() {
    import('rxjs').then(({ forkJoin }) => {
      const headers = this.getHeaders();
      
      // Cargar solicitud con manejo de error para usuarios que no se registraron como comercio
      this.auth.getMiaSolicitudComerc().subscribe({
        next: (solicitud) => {
          this.solicitudComerc = solicitud;
          this.loadDashboardData();
        },
        error: () => {
          // Si no hay solicitud, simplemente continuamos
          this.solicitudComerc = null;
          this.loadDashboardData();
        }
      });
    });
  }

  loadDashboardData() {
    import('rxjs').then(({ forkJoin }) => {
      forkJoin([
        this.http.get<any[]>(`${environment.apiUrl}/les-meves-ofertes`, { headers: this.getHeaders() }),
        this.auth.getElMeuComerc(),
        this.auth.getCategories(),
        this.http.get<any>(`${environment.apiUrl}/comerc/vendes`, { headers: this.getHeaders() })
      ]).subscribe({
        next: ([ofertes, comerc, categories, estadistiques]) => {
          this.ofertes = ofertes;
          this.comerc = comerc;
          this.categories = categories;
          this.estadistiques = estadistiques;
          this.carregant = false;
        },
        error: (err) => {
          console.error('Error carregant dades del panell:', err);
          this.carregant = false;
        }
      });
    });
  }

  ngOnDestroy() {
    if (this.mostrantCamera) {
      this.tancarCamera();
    }
  }

  esProgramadaEnFutur(oferta: any): boolean {
    if (!oferta.data_publicacio) return false;
    const dataPub = new Date(oferta.data_publicacio);
    return dataPub > new Date();
  }

  private getHeaders() {
    return new HttpHeaders({
      'Authorization': `Bearer ${this.auth.obtenirToken()}`
    });
  }

  // --- CÀMERA QR ---

  obrirCamera() {
    this.mostrantCamera = true;
    this.codiEscanejat = null;
    this.qrTokenXifrat = '';
    this.importCompra = null;
    this.missatgeCamera = '';
  }

  tancarCamera() {
    this.mostrantCamera = false;
  }

  onQREscanejat(resultat: string) {
    if (this.mostrantAccionsQR || this.qrTokenXifrat) return;
    this.qrTokenXifrat = resultat;
    this.tancarCamera();
    this.mostrantAccionsQR = true;
  }

  tancarAccionsQR() {
    this.mostrantAccionsQR = false;
    this.qrTokenXifrat = '';
    this.importCompra = null;
  }

  mostrarAlertaCamera(text: string, tipus: 'success' | 'error') {
    this.missatgeCamera = text;
    this.tipusMissatgeCamera = tipus;
    setTimeout(() => this.missatgeCamera = '', 4500);
  }

  anarADonarPunts() {
    if (!this.importCompra || this.importCompra <= 0) {
      this.mostrarAlertaCamera("Si us plau, introdueix un import vàlid per a la compra.", 'error');
      return;
    }

    const payload = {
      qr_token:      this.qrTokenXifrat,
      import_compra: this.importCompra
    };

    this.http.post(`${environment.apiUrl}/comerc/atorgar-punts`, payload, { headers: this.getHeaders() })
      .subscribe({
        next: (res: any) => {
          this.mostrarAlertaCamera(`✅ ${res.missatge}`, 'success');
          this.tancarAccionsQR();
          this.carregarEstadistiques();
        },
        error: (err) => {
          this.mostrarAlertaCamera(`❌ ${err.error.missatge || 'Codi invàlid.'}`, 'error');
          this.tancarAccionsQR();
        }
      });
  }

  anarABescanviarOferta() {
    const payload = { qr_token: this.qrTokenXifrat };

    this.http.post(`${environment.apiUrl}/comerc/validar-oferta`, payload, { headers: this.getHeaders() })
      .subscribe({
        next: (res: any) => {
          this.mostrarAlertaCamera(`✅ Oferta Validada! Lliura el producte: ${res.oferta}`, 'success');
          this.tancarAccionsQR();
          this.carregarEstadistiques();
        },
        error: (err) => {
          this.mostrarAlertaCamera(`❌ ${err.error.missatge || 'Codi d\'oferta invàlid o caducat.'}`, 'error');
          this.tancarAccionsQR();
        }
      });
  }

  // --- OFERTES ---

  carregarLesMevesOfertes() {
    this.http.get<any[]>(`${environment.apiUrl}/les-meves-ofertes`, { headers: this.getHeaders() })
      .subscribe({
        next: (res) => { this.ofertes = res; },
        error: (err) => console.error('Error carregant les ofertes:', err)
      });
  }

  eliminarOferta(id: number) {
    this.ofertaEliminarId = id;
    this.confirmDeleteVisible = true;
  }

  confirmarEliminar() {
    if (this.ofertaEliminarId === null) return;
    this.http.delete(`${environment.apiUrl}/ofertes/${this.ofertaEliminarId}`, { headers: this.getHeaders() })
      .subscribe({
        next: () => {
          this.ofertes = this.ofertes.filter(o => o.id_oferta !== this.ofertaEliminarId);
          this.confirmDeleteVisible = false;
          this.ofertaEliminarId = null;
        },
        error: () => {
          alert('Error eliminant l\'oferta.');
          this.confirmDeleteVisible = false;
          this.ofertaEliminarId = null;
        }
      });
  }

  cancelarEliminar() {
    this.confirmDeleteVisible = false;
    this.ofertaEliminarId = null;
  }
  obrirModalEdicio(oferta: any) {
    this.ofertaEditantId = oferta.id_oferta;
    this.imatgeSeleccionada = null;
    this.editForm.patchValue({
      titol:      oferta.titol,
      descripcio: oferta.descripcio || '',
      cost_punts: oferta.cost_punts,
      data_fi:    oferta.data_fi ? oferta.data_fi.split('T')[0] : ''
    });
    this.mostrantModal = true;
  }

  onFileSelected(event: any) {
    const file: File = event.target.files[0];
    if (file) this.imatgeSeleccionada = file;
  }

  tancarModal() {
    this.mostrantModal = false;
    this.ofertaEditantId = null;
    this.imatgeSeleccionada = null;
    this.editForm.reset();
  }

  guardarCanvis() {
    if (this.editForm.invalid || !this.ofertaEditantId) return;

    const formData = new FormData();
    formData.append('titol',      this.editForm.get('titol')?.value);
    formData.append('cost_punts', this.editForm.get('cost_punts')?.value.toString());
    if (this.editForm.get('descripcio')?.value) {
      formData.append('descripcio', this.editForm.get('descripcio')?.value);
    }
    if (this.editForm.get('data_fi')?.value) {
      formData.append('data_fi', this.editForm.get('data_fi')?.value);
    }
    if (this.imatgeSeleccionada) {
      formData.append('imatge', this.imatgeSeleccionada);
    }
    formData.append('_method', 'PUT');

    this.http.post(`${environment.apiUrl}/ofertes/${this.ofertaEditantId}`, formData, { headers: this.getHeaders() })
      .subscribe({
        next: () => {
          this.tancarModal();
          this.carregarLesMevesOfertes();
        },
        error: () => alert('Error en guardar els canvis')
      });
  }

  // --- CREAR OFERTA ---

  obrirModalCrear() {
    this.crearForm.reset({
      titol: '', descripcio: '', cost_punts: 50,
      tipus_durada: 'sempre', data_personalitzada: '',
      programar: false, data_publicacio: ''
    });
    this.missatgeCrear = '';
    this.mostrantModalCrear = true;
  }

  tancarModalCrear() {
    this.mostrantModalCrear = false;
  }

  publicarOferta() {
    if (this.crearForm.invalid) return;
    this.loadingCrear = true;

    let dataFi = null;
    const valors = this.crearForm.value;
    if (valors.tipus_durada !== 'sempre') {
      const avui = new Date();
      if (valors.tipus_durada === '1d') avui.setDate(avui.getDate() + 1);
      if (valors.tipus_durada === '3d') avui.setDate(avui.getDate() + 3);
      if (valors.tipus_durada === '1s') avui.setDate(avui.getDate() + 7);
      if (valors.tipus_durada === '1m') avui.setMonth(avui.getMonth() + 1);
      dataFi = valors.tipus_durada === 'custom'
               ? valors.data_personalitzada
               : avui.toISOString().split('T')[0];
    }

    let dataPublicacio = null;
    if (valors.programar && valors.data_publicacio) {
      dataPublicacio = valors.data_publicacio.replace('T', ' ');
      if (dataPublicacio.length === 16) {
        dataPublicacio += ':00';
      }
    }

    const payload = {
      titol:      valors.titol,
      descripcio: valors.descripcio,
      cost_punts: valors.cost_punts,
      data_fi:    dataFi,
      data_publicacio: dataPublicacio
    };

    this.http.post(`${environment.apiUrl}/ofertes`, payload, { headers: this.getHeaders() }).subscribe({
      next: () => {
        this.loadingCrear = false;
        this.errorCrear = false;
        this.missatgeCrear = valors.programar ? '🎉 Oferta programada amb èxit!' : '🎉 Oferta publicada amb èxit!';
        this.carregarLesMevesOfertes();
        setTimeout(() => this.tancarModalCrear(), 2000);
      },
      error: (err) => {
        this.loadingCrear = false;
        this.errorCrear = true;
        this.missatgeCrear = err.error?.message || 'Error en publicar. Revisa les dades.';
      }
    });
  }

  // --- COMERÇ ---

  carregarElMeuComerc() {
    this.auth.getElMeuComerc().subscribe({
      next: (res) => {
        this.comerc = res;
      },
      error: (err) => console.error('Error carregant el comerç:', err)
    });
  }

  carregarCategories() {
    this.auth.getCategories().subscribe({
      next: (res) => this.categories = res,
      error: (err) => console.error('Error carregant categories:', err)
    });
  }

  obrirModalComerc() {
    if (this.comerc) {
      // Omplim tots els camps, incloent els nous de contacte
      this.comercForm.patchValue({
        nom_comercial:  this.comerc.nom_comercial  || '',
        id_categoria:   this.comerc.id_categoria   || '',
        cif:            this.comerc.cif            || '',
        descripcio:     this.comerc.descripcio     || '',
        telefon:        this.comerc.telefon        || '',
        email_contacte: this.comerc.email_contacte || '',
        enllac_web:     this.comerc.enllac_web     || '',
        instagram:      this.comerc.instagram      || '',
      });
    } else {
      this.carregarElMeuComerc();
    }
    this.imatgeComercSeleccionada = null;
    this.imatgeComercPreview = null;
    this.mostrantModalComerc = true;
  }

  tancarModalComerc() {
    this.mostrantModalComerc = false;
    this.imatgeComercSeleccionada = null;
    this.imatgeComercPreview = null;
  }

  onFileComercSelected(event: any) {
    const file: File = event.target.files[0];
    if (file) {
      this.imatgeComercSeleccionada = file;
      const reader = new FileReader();
      reader.onload = (e: any) => {
        this.imatgeComercPreview = e.target.result;
      };
      reader.readAsDataURL(file);
    }
  }

  guardarCanvisComerc() {
    if (this.comercForm.invalid) return;

    const formData = new FormData();
    const vals = this.comercForm.value;

    // Camps bàsics
    formData.append('nom_comercial', vals.nom_comercial);
    formData.append('id_categoria',  vals.id_categoria);
    formData.append('cif',           vals.cif);

    // Camps de contacte (els enviem sempre, fins i tot si estan buits,
    // perquè l'usuari pot voler esborrar un valor existent)
    formData.append('descripcio',     vals.descripcio     || '');
    formData.append('telefon',        vals.telefon        || '');
    formData.append('email_contacte', vals.email_contacte || '');
    formData.append('enllac_web',     vals.enllac_web     || '');
    formData.append('instagram',      vals.instagram      || '');

    if (this.imatgeComercSeleccionada) {
      formData.append('imatge', this.imatgeComercSeleccionada);
    }

    this.auth.actualitzarComerc(formData).subscribe({
      next: (res) => {
        this.comerc = res.comerc;
        this.tancarModalComerc();
        alert('Comerç actualitzat correctament');
      },
      error: (err) => {
        console.error('Error actualitzant el comerç:', err);
        alert('Error actualitzant el comerç. Revisa la consola.');
      }
    });
  }

  // --- ESTADÍSTIQUES ---

  carregarEstadistiques() {
    this.http.get<any>(`${environment.apiUrl}/comerc/vendes`, { headers: this.getHeaders() })
      .subscribe({
        next: (res) => { this.estadistiques = res; },
        error: (err) => console.error('Error carregant les estadístiques', err)
      });
  }
}