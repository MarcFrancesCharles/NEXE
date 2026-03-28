import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { FormsModule } from '@angular/forms';
import { Auth } from '../../core/services/auth';
import { QRCodeComponent } from 'angularx-qrcode'; 

@Component({
  selector: 'app-profile',
  standalone: true,
  imports: [CommonModule, FormsModule, QRCodeComponent], 
  templateUrl: './profile.html',
  styleUrl: './profile.css'
})
export class Profile implements OnInit {
  usuari: any = null;
  qrTokenCarnet: string = ''; // VARIABLE PEL NOU CARNET DIGITAL
  carregant: boolean = true;

  private http = inject(HttpClient);
  private auth = inject(Auth);

  ngOnInit() {
    this.obtenirDadesPerfil();
    
    // Si l'usuari és un client normal (ESTANDARD), demanem el seu Carnet Digital
    if (this.auth.obtenirRol() === 'ESTANDARD') {
      this.carregarQr();
    }
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

  // NOVA FUNCIÓ: Cridem l'API per obtenir el token xifrat
  carregarQr() {
    this.auth.obtenirCarnetQr().subscribe({
      next: (res) => {
        this.qrTokenCarnet = res.qr_token;
      },
      error: (err) => console.error('Error carregant el carnet QR', err)
    });
  }

  // =======================================================
  // EDICIÓ DEL PERFIL
  // =======================================================
  editantPerfil: boolean = false;
  editDades = {
    nom: '',
    correu: '',
    contrasenya: ''
  };
  missatgeEdit: string = '';
  tipusMissatgeEdit: 'success' | 'error' | '' = '';

  obrirEdicio() {
    this.editDades.nom = this.usuari?.nom || '';
    this.editDades.correu = this.usuari?.correu || '';
    this.editDades.contrasenya = '';
    this.missatgeEdit = '';
    this.editantPerfil = true;
  }

  tancarEdicio() {
    this.editantPerfil = false;
    this.missatgeEdit = '';
  }

  guardarPerfil() {
    const payload: any = {
      nom: this.editDades.nom,
      correu: this.editDades.correu
    };

    if (this.editDades.contrasenya && this.editDades.contrasenya.trim().length > 0) {
      payload.contrasenya = this.editDades.contrasenya;
    }

    this.http.put('http://localhost:8000/api/perfil-meu', payload, { headers: this.getHeaders() })
      .subscribe({
        next: (res: any) => {
          this.missatgeEdit = 'Perfil actualitzat correctament';
          this.tipusMissatgeEdit = 'success';
          this.obtenirDadesPerfil(); // Refresca les dades de l'usuari
          setTimeout(() => this.tancarEdicio(), 2000); // Tanca després de 2 segons automàticament
        },
        error: (err) => {
          this.missatgeEdit = err.error.message || 'Error actualitzant el perfil';
          this.tipusMissatgeEdit = 'error';
        }
      });
  }
}