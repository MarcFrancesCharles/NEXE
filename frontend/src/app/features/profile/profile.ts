import { Component as NgComponent, inject as NgInject, OnInit as NgOnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { FormsModule } from '@angular/forms';
import { Auth } from '../../core/services/auth';

@NgComponent({
  selector: 'app-profile',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './profile.html',
  styleUrl: './profile.css'
})
export class Profile implements NgOnInit {
  usuari: any = null;
  codiQR: string = '';
  missatge: string = '';
  tipusMissatge: 'success' | 'error' | '' = '';
  carregant: boolean = true;

  private http = NgInject(HttpClient);
  private auth = NgInject(Auth);

  ngOnInit() {
    this.obtenirDadesPerfil();
  }

  // Creem els headers amb el Token per parlar amb Laravel
  private getHeaders() {
    return new HttpHeaders({
      'Authorization': `Bearer ${this.auth.obtenirToken()}`
    });
  }

  obtenirDadesPerfil() {
    this.http.get('http://localhost:8000/api/perfil-meu', { headers: this.getHeaders() })
      .subscribe({
        next: (res: any) => {
          this.usuari = res;
          this.carregant = false;
        },
        error: () => this.carregant = false
      });
  }

  validarTiquet() {
    if (!this.codiQR) return;

    this.http.post('http://localhost:8000/api/tiquets/escanejar', 
      { codi_qr: this.codiQR }, 
      { headers: this.getHeaders() }
    ).subscribe({
      next: (res: any) => {
        this.missatge = 'Tiquet validat! Has sumat punts.';
        this.tipusMissatge = 'success';
        this.codiQR = '';
        this.obtenirDadesPerfil(); // Refresquem els punts automàticament
      },
      error: (err) => {
        this.missatge = err.error.missatge || 'Codi no vàlid o ja utilitzat.';
        this.tipusMissatge = 'error';
      }
    });
  }
}