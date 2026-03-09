import { inject } from '@angular/core';
import { Router, CanActivateFn } from '@angular/router';
import { Auth } from '../services/auth';

export const roleGuard: CanActivateFn = (route, state) => {
  const auth = inject(Auth);
  const router = inject(Router);
  const rol = auth.obtenirRol();
  
  // Ara sabem segur que és COMERÇ gràcies a la teva captura
  if (rol === 'COMERÇ' || rol === 'ADMIN') {
    return true;
  } else {
    router.navigate(['/perfil']);
    return false;
  }
};