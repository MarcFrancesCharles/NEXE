import { Component, inject } from '@angular/core';
import { Router, RouterOutlet, RouterLink, RouterLinkActive } from '@angular/router';
import { Auth } from './core/services/auth';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, RouterLink, RouterLinkActive],
  templateUrl: './app.html',
  styleUrl: './app.css'
})
export class App {
  private auth = inject(Auth);
  private router = inject(Router);

  // Funció per saber si l'usuari ha iniciat sessió
  estaLogat(): boolean {
    return !!this.auth.obtenirToken();
  }

  // Funció per saber si l'usuari és administrador o comerç
  esAdmin(): boolean {
    const rol = this.auth.obtenirRol();
    return rol === 'ADMIN' || rol === 'COMERC';
  }

  // Funció per tancar sessió
  sortir() {
    this.auth.logout();
    this.router.navigate(['/login']);
  }
}