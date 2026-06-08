import { Component, inject } from '@angular/core';
import { Router, RouterOutlet, RouterLink, RouterLinkActive } from '@angular/router';
import { Auth } from './core/services/auth';
import { Footer } from './core/components/footer/footer'; // Ajusta la ruta si es necesario

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, RouterLink, RouterLinkActive, Footer],
  templateUrl: './app.html',
  styleUrl: './app.css'
})
export class App {
  private auth = inject(Auth);
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

  // Funció per tancar sessió
  sortir() {
    this.auth.logout();
    this.router.navigate(['/login']);
  }
}