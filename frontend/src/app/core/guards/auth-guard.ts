import { inject } from '@angular/core';
import { Router, CanActivateFn } from '@angular/router';
import { Auth } from '../services/auth'; 

export const authGuard: CanActivateFn = (route, state) => {
  const auth = inject(Auth);
  const router = inject(Router);

  const token = auth.obtenirToken();
  console.log('--- CONTROL DE SEGURETAT ---');
  console.log('Token trobat:', token ? 'SÍ ✅' : 'NO ❌');

  if (token) {
    return true; // Deixem passar
  } else {
    console.error('Accés denegat: Redirigint al Login...');
    router.navigate(['/login']);
    return false; // Bloquegem
  }
};