import { Component, inject, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { RouterLink } from '@angular/router';
import { Auth } from '../../core/services/auth';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators, FormsModule } from '@angular/forms';
import { ZXingScannerModule } from '@zxing/ngx-scanner';
import { BarcodeFormat } from '@zxing/library';
import { environment } from '../../../../environments/environment';

@Component({
  selector: 'app-shop',
  standalone: true,
  imports: [CommonModule, RouterLink, ReactiveFormsModule, FormsModule, ZXingScannerModule],
  templateUrl: './shop.html',
  styleUrl: './shop.css'
})
export class Shop implements OnInit, OnDestroy {

  storageUrl = environment.storageUrl;
  ofertes: any[] = [];
  termeCerca: string = '';
  mostrantModal = false;

  get ofertesFiltrades() {
    if (!this.termeCerca.trim()) return this.ofertes;
    const term = this.termeCerca.toLowerCase().trim();
    return this.ofertes.filter(o => o.titol?.toLowerCase().includes(term));
  }
  
  // Variables per la Càmera QR
  mostrantCamera = false;
  codiEscanejat: string | null = null;
  formatsPermesos = [BarcodeFormat.QR_CODE];

  // Variables pel Modal d'Accions QR (Seguretat Zero-Trust)
  mostrantAccionsQR = false;
  qrTokenXifrat: string = ''; 
  importCompra: number | null = null; // Guardarà l'import introduït pel botiguer
  
  // Missatges d'èxit o error a la càmera
  missatgeCamera: string = '';
  tipusMissatgeCamera: 'success' | 'error' | '' = '';

  ofertaEditantId: number | null = null;
  editForm: FormGroup;
  imatgeSeleccionada: File | null = null;
  
  // Variables pel Comerç
  comerc: any = null;
  categories: any[] = [];
  mostrantModalComerc = false;
  comercForm: FormGroup;
  imatgeComercSeleccionada: File | null = null;
  imatgeComercPreview: string | null = null;
  
  private http = inject(HttpClient);
  private auth = inject(Auth);
  private fb = inject(FormBuilder);

  // Variables de les Estadístiques
  estadistiques: any = {
    punts_donats: 0,
    ofertes_venudes: 0,
    punts_bescanviats: 0,
    historial_vendes: []
  };  

  constructor() {
    this.editForm = this.fb.group({
      titol: ['', [Validators.required]],
      descripcio: [''],
      cost_punts: [1, [Validators.required, Validators.min(1)]],
      data_fi: ['']
    });

    this.comercForm = this.fb.group({
      nom_comercial: ['', [Validators.required]],
      id_categoria: ['', [Validators.required]],
      cif: ['', [Validators.required]]
    });
  }

  ngOnInit() {
    this.carregarLesMevesOfertes();
    this.carregarElMeuComerc();
    this.carregarCategories();
    this.carregarEstadistiques();
  }

  ngOnDestroy() {
    if (this.mostrantCamera) {
      this.tancarCamera();
    }
  }

  private getHeaders() {
    return new HttpHeaders({
      'Authorization': `Bearer ${this.auth.obtenirToken()}`
    });
  }

  // --- FUNCIONS DE LA CÀMERA QR ---

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

  // Aquesta funció salta automàticament quan la càmera llegeix un QR
  onQREscanejat(resultat: string) {
    if (this.mostrantAccionsQR || this.qrTokenXifrat) return; // Evitem lectures repetides

    this.qrTokenXifrat = resultat; // És el text encriptat que ens envia el client
    this.tancarCamera(); 
    
    // Obrim el modal on el botiguer tria què vol fer
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
    setTimeout(() => this.missatgeCamera = '', 4500); // Amaga l'avís passats 4 segons
  }

  // --- FUNCIONS DE SEGURETAT ZERO-TRUST (Connexió Backend) ---

  anarADonarPunts() {
    if (!this.importCompra || this.importCompra <= 0) {
      this.mostrarAlertaCamera("Si us plau, introdueix un import vàlid per a la compra.", 'error');
      return;
    }

    const payload = {
      qr_token: this.qrTokenXifrat,
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
    const payload = {
      qr_token: this.qrTokenXifrat
    };

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

  // --- FUNCIONS DE GESTIÓ D'OFERTES ---

  carregarLesMevesOfertes() {
    this.http.get<any[]>(`${environment.apiUrl}/les-meves-ofertes`, { headers: this.getHeaders() })
      .subscribe({
        next: (res) => { this.ofertes = res; },
        error: (err) => console.error('Error carregant les ofertes:', err)
      });
  }

  eliminarOferta(id: number) {
    if (confirm('Estàs segur que vols eliminar aquesta oferta?')) {
      this.http.delete(`${environment.apiUrl}/ofertes/${id}`, { headers: this.getHeaders() })
        .subscribe({
          next: () => { this.ofertes = this.ofertes.filter(o => o.id_oferta !== id); },
          error: (err) => alert('Error eliminant l\'oferta.')
        });
    }
  }

  // --- FUNCIONS DEL POP-UP D'EDICIÓ ---

  obrirModalEdicio(oferta: any) {
    this.ofertaEditantId = oferta.id_oferta;
    this.imatgeSeleccionada = null; 
    
    this.editForm.patchValue({
      titol: oferta.titol,
      descripcio: oferta.descripcio || '',
      cost_punts: oferta.cost_punts,
      data_fi: oferta.data_fi ? oferta.data_fi.split('T')[0] : ''
    });
    
    this.mostrantModal = true;
  }

  onFileSelected(event: any) {
    const file: File = event.target.files[0];
    if (file) {
      this.imatgeSeleccionada = file;
    }
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
    formData.append('titol', this.editForm.get('titol')?.value);
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
        error: (err) => alert('Error en guardar els canvis')
      });
  }

  // --- FUNCIONS DE GESTIÓ DEL COMERÇ ---

  carregarElMeuComerc() {
    this.auth.getElMeuComerc().subscribe({
      next: (res) => {
        this.comerc = res;
        if (this.comerc && this.mostrantModalComerc) {
          this.comercForm.patchValue({
            nom_comercial: this.comerc.nom_comercial,
            id_categoria: this.comerc.id_categoria,
            cif: this.comerc.cif
          });
        }
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
      this.comercForm.patchValue({
        nom_comercial: this.comerc.nom_comercial,
        id_categoria: this.comerc.id_categoria,
        cif: this.comerc.cif
      });
    } else {
      this.carregarElMeuComerc();
    }
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
    formData.append('nom_comercial', this.comercForm.get('nom_comercial')?.value);
    formData.append('id_categoria', this.comercForm.get('id_categoria')?.value);
    formData.append('cif', this.comercForm.get('cif')?.value);

    if (this.imatgeComercSeleccionada) {
      formData.append('imatge', this.imatgeComercSeleccionada);
    }
    //Fem guardar el comerç com si fos una actualització, encara que sigui la primera vegada que el crea. El backend s'encarrega de diferenciar-ho.
    formData.append('_method', 'PUT');

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
    // --- FUNCIONS D'ESTADÍSTIQUES ---
  carregarEstadistiques() {
    this.http.get<any>(`${environment.apiUrl}/comerces/vendes`, { headers: this.getHeaders() })
      .subscribe({
        next: (res) => {
          this.estadistiques = res;
        },
        error: (err) => console.error('Error carregant les estadístiques', err)
      });
  }
  

}