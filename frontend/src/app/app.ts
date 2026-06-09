import { Component, inject, OnInit } from '@angular/core';
import { Router, RouterOutlet, RouterLink, RouterLinkActive, NavigationEnd } from '@angular/router';
import { Auth } from './core/services/auth';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '../../environments/environment';
import { Footer } from './core/components/footer/footer';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, RouterLink, RouterLinkActive, Footer],
  templateUrl: './app.html',
  styleUrl: './app.css'
})
export class App implements OnInit {
  private auth = inject(Auth);
  private http = inject(HttpClient);

  // User name and points for menu
  nomUsuari: string = '';
  puntsUsuari: number = 0;
  usuariBloquejat: boolean = false;

  private getHeaders() {
    return new HttpHeaders({
      'Authorization': `Bearer ${this.auth.obtenirToken()}`
    });
  }
  private router = inject(Router);

  isSidebarCollapsed: boolean = false;

  toggleSidebar() {
    this.isSidebarCollapsed = !this.isSidebarCollapsed;
  }

  // Funció per saber si l'usuari ha iniciat sessió
  estaLogat(): boolean {
    return !!this.auth.obtenirToken();
  }

  // Funció per saber si l'usuari és administrador o comerç
  esAdmin(): boolean {
    const rol = this.auth.obtenirRol();
    return rol === 'ADMIN' || rol === 'COMERC';
  }

  esAdminReal(): boolean {
    return this.auth.obtenirRol() === 'ADMIN';
  }

  esComercReal(): boolean {
    return this.auth.obtenirRol() === 'COMERC';
  }

  esUsuariNormal(): boolean {
    return this.auth.esNormalUser();
  }

  // Funció per carregar el perfil
  carregarPerfil() {
    if (!this.estaLogat()) {
      this.usuariBloquejat = false;
      this.nomUsuari = '';
      this.puntsUsuari = 0;
      return;
    }
    this.http.get<any>(`${environment.apiUrl}/perfil-meu`, { headers: this.getHeaders() })
      .subscribe({
        next: (res: any) => {
          this.nomUsuari = res.nom || '';
          this.puntsUsuari = res.perfil?.punts_totals || 0;
          this.usuariBloquejat = res.estat === 'BLOQUEJAT';
        },
        error: (err) => {
          if (err.status === 401) {
            this.auth.logout();
            this.usuariBloquejat = false;
          }
        }
      });
  }

  // Funció d'inicialització
  ngOnInit() {
    this.carregarPerfil();
    
    // Escolta canvis de ruta per recarregar el perfil i verificar si està bloquejat després de fer login
    this.router.events.subscribe(event => {
      if (event instanceof NavigationEnd) {
        this.carregarPerfil();
      }
    });
  }

  // Funció per tancar sessió
  sortir() {
    this.auth.logout();
    this.usuariBloquejat = false;
    this.router.navigate(['/login']);
  }
}