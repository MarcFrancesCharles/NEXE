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

  // Estat per editar el perfil
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
          this.obtenirDadesPerfil(); // Refresh user data
          setTimeout(() => this.tancarEdicio(), 2000); // Close after 2 seconds automatically
        },
        error: (err) => {
          this.missatgeEdit = err.error.message || 'Error actualitzant el perfil';
          this.tipusMissatgeEdit = 'error';
        }
      });
  }
}