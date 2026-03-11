import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Router, RouterLink } from '@angular/router';
import { Auth } from '../../../core/services/auth';

@Component({
  selector: 'app-create-offer',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterLink],
  templateUrl: './create-offer.html',
  styleUrl: './create-offer.css'
})
export class CreateOffer {
  ofertaForm: FormGroup;
  loading = false;
  missatge = '';
  error = false;

  private fb = inject(FormBuilder);
  private http = inject(HttpClient);
  private router = inject(Router);
  private auth = inject(Auth);

  constructor() {
    this.ofertaForm = this.fb.group({
      titol: ['', [Validators.required, Validators.minLength(5)]],
      descripcio: ['', [Validators.required]],
      cost_punts: [50, [Validators.required, Validators.min(1)]],
      tipus_durada: ['sempre'], // Per defecte no caduca
      data_personalitzada: [''] // Només s'usa si trien "personalitzat"
    });
  }

  crearOferta() {
    if (this.ofertaForm.invalid) return;
    this.loading = true;
    
    // 1. Calcular la data de caducitat segons què hagin triat
    let dataFi = null;
    const valors = this.ofertaForm.value;
    
    if (valors.tipus_durada !== 'sempre') {
      const avui = new Date();
      if (valors.tipus_durada === '1d') avui.setDate(avui.getDate() + 1);
      if (valors.tipus_durada === '3d') avui.setDate(avui.getDate() + 3);
      if (valors.tipus_durada === '1s') avui.setDate(avui.getDate() + 7);
      if (valors.tipus_durada === '1m') avui.setMonth(avui.getMonth() + 1);
      
      // Formategem la data a YYYY-MM-DD perquè Laravel ho entengui bé
      dataFi = valors.tipus_durada === 'custom' 
               ? valors.data_personalitzada 
               : avui.toISOString().split('T')[0]; 
    }

    // 2. Preparar el paquet final per a Laravel
    const paquetFinal = {
      titol: valors.titol,
      descripcio: valors.descripcio,
      cost_punts: valors.cost_punts,
      data_fi: dataFi
    };

    const headers = new HttpHeaders({ 'Authorization': `Bearer ${this.auth.obtenirToken()}` });
    console.log(paquetFinal);

    this.http.post('http://localhost:8000/api/ofertes', paquetFinal, { headers }).subscribe({
      next: () => {
        this.loading = false;
        this.error = false;
        this.missatge = '🎉 Oferta publicada amb èxit!';
        setTimeout(() => this.router.navigate(['/la-meva-botiga']), 2000);
      },
      error: (err) => {
        this.loading = false;
        this.error = true;
        this.missatge = err.error?.message || 'Error en publicar. Revisa les dades.';
        console.error('Error al crear oferta:', err);
      }
    });
  }
}