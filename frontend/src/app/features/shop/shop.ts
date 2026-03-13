import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { RouterLink } from '@angular/router';
import { Auth } from '../../core/services/auth';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ZXingScannerModule } from '@zxing/ngx-scanner';
import { BarcodeFormat } from '@zxing/library';

@Component({
  selector: 'app-shop',
  standalone: true,
  imports: [CommonModule, RouterLink, ReactiveFormsModule, ZXingScannerModule],
  templateUrl: './shop.html',
  styleUrl: './shop.css'
})
export class Shop implements OnInit {
  ofertes: any[] = [];
  mostrantModal = false;
  
  // Variables per la Càmera QR
  mostrantCamera = false;
  codiEscanejat: string | null = null;
  formatsPermesos = [BarcodeFormat.QR_CODE];

  // Variables pel Modal d'Accions QR
  mostrantAccionsQR = false;
  usuariEscanejat: string | null = null;

  ofertaEditantId: number | null = null;
  editForm: FormGroup;
  imatgeSeleccionada: File | null = null; // Guardarà la foto
  
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
  }

  // --- FUNCIONS DE LA CÀMERA QR ---

  // Funció per obrir la càmera
  obrirCamera() {
    this.mostrantCamera = true;
    this.codiEscanejat = null; // Netegem l'escaneig anterior
  }

  // Funció per tancar la càmera
  tancarCamera() {
    this.mostrantCamera = false;
  }

  // Aquesta funció saltarà AUTOMÀTICAMENT quan la càmera llegeixi un QR
  onQREscanejat(resultat: string) {
    console.log('Codi detectat!', resultat);
    this.codiEscanejat = resultat;
    this.tancarCamera(); // Tanquem la càmera
    
    // Obrim el modal d'accions amb l'usuari escanejat
    this.usuariEscanejat = resultat;
    this.mostrantAccionsQR = true;
  }

  // --- FUNCIONS DEL MODAL D'ACCIONS QR ---

  tancarAccionsQR() {
    this.mostrantAccionsQR = false;
    this.usuariEscanejat = null;
  }

  anarADonarPunts() {
    alert('Has triat donar punts al client: ' + this.usuariEscanejat);
    // Aquí hi anirà la lògica per donar punts (ex: obrir un altre modal o navegar)
    this.tancarAccionsQR();
  }

  anarABescanviarOferta() {
    alert('Has triat bescanviar oferta pel client: ' + this.usuariEscanejat);
    // Aquí hi anirà la lògica per bescanviar l'oferta
    this.tancarAccionsQR();
  }

  // --- FUNCIONS DE GESTIÓ D'OFERTES ---

  carregarLesMevesOfertes() {
    const headers = new HttpHeaders({ 'Authorization': `Bearer ${this.auth.obtenirToken()}` });
    this.http.get<any[]>('http://localhost:8000/api/les-meves-ofertes', { headers })
      .subscribe({
        next: (res) => { this.ofertes = res; },
        error: (err) => console.error('Error carregant les ofertes:', err)
      });
  }

  eliminarOferta(id: number) {
    if (confirm('Estàs segur que vols eliminar aquesta oferta?')) {
      const headers = new HttpHeaders({ 'Authorization': `Bearer ${this.auth.obtenirToken()}` });
      this.http.delete(`http://localhost:8000/api/ofertes/${id}`, { headers })
        .subscribe({
          next: () => { this.ofertes = this.ofertes.filter(o => o.id_oferta !== id); },
          error: (err) => alert('Error eliminant l\'oferta.')
        });
    }
  }

  // --- FUNCIONS DEL POP-UP D'EDICIÓ ---

  obrirModalEdicio(oferta: any) {
    this.ofertaEditantId = oferta.id_oferta;
    this.imatgeSeleccionada = null; // Netegem si hi ha foto vella
    
    this.editForm.patchValue({
      titol: oferta.titol,
      descripcio: oferta.descripcio || '',
      cost_punts: oferta.cost_punts,
      data_fi: oferta.data_fi ? oferta.data_fi.split('T')[0] : ''
    });
    
    this.mostrantModal = true;
  }

  // Detecta l'arxiu quan el selecciones
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

    const headers = new HttpHeaders({ 'Authorization': `Bearer ${this.auth.obtenirToken()}` });
    
    // CREEM EL FORMDATA PER ENVIAR LA IMATGE
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

    formData.append('_method', 'PUT'); // Truc de Laravel

    this.http.post(`http://localhost:8000/api/ofertes/${this.ofertaEditantId}`, formData, { headers })
      .subscribe({
        next: () => {
          this.tancarModal();
          this.carregarLesMevesOfertes(); // Recarrega i mostra la foto
        },
        error: (err) => alert('Error en guardar els canvis')
      });
  }

  // --- FUNCIONS DE GESTIÓ DEL COMERÇ ---

  carregarElMeuComerc() {
    this.auth.getElMeuComerc().subscribe({
      next: (res) => {
        this.comerc = res;
        // Si el modal ja estava obert o s'està obrint, punxem les dades
        if (this.comerc) {
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
    console.log('Obrint modal. Dades actuals:', this.comerc);
    
    // Si tenim dades, les posem ja
    if (this.comerc) {
      this.comercForm.patchValue({
        nom_comercial: this.comerc.nom_comercial,
        id_categoria: this.comerc.id_categoria,
        cif: this.comerc.cif
      });
    } else {
      // Si no en tenim, les demanem (el subscribe de dalt ja les posarà)
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
      
      // Crear preview
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
    
    // Només afegim si han canviat o per seguretat els actuals
    formData.append('nom_comercial', this.comercForm.get('nom_comercial')?.value);
    formData.append('id_categoria', this.comercForm.get('id_categoria')?.value);
    formData.append('cif', this.comercForm.get('cif')?.value);

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
        if (err.error && err.error.errors) {
          const errors = Object.values(err.error.errors).flat().join('\n');
          alert('Error de validació:\n' + errors);
        } else {
          alert('Error actualitzant el comerç. Revisa la consola.');
        }
      }
    });
  }
}