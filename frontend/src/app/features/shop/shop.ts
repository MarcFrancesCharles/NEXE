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

  // Funció per obrir la càmera
  obrirCamera() {
    alert('EL BOTÓ FUNCIONA!'); // Afegeix aquesta línia temporalment
    this.mostrantCamera = true;
    this.codiEscanejat = null; // Netegem l'escaneig anterior
  }

  // Funció per tancar la càmera
  tancarCamera() {
    this.mostrantCamera = false;
  }

  // Aquesta funció saltarà AUTOMÀTICAMENT quan la càmera llegeixi un QR
  onQREscanejat(resultat: string) {
    // Fem un "beep" mental
    console.log('Codi detectat!', resultat);
    this.codiEscanejat = resultat;
    this.tancarCamera(); // Tanquem la càmera
    
    // Aquí obrirem el següent pas (Donar punts o Bescanviar oferta)
    alert('QR Detectat! El codi del client és: ' + resultat);
  }
  ofertaEditantId: number | null = null;
  editForm: FormGroup;
  imatgeSeleccionada: File | null = null; // Guardarà la foto
  
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
  }

  ngOnInit() {
    this.carregarLesMevesOfertes();
  }

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

  // --- FUNCIONS DEL POP-UP ---

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
}