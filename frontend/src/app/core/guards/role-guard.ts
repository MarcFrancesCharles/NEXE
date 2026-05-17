import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { Auth } from '../services/auth';

export const roleGuard: CanActivateFn = (route, state) => {
  const router = inject(Router);
  const auth = inject(Auth);
  
  const userRole = auth.obtenirRol();
  
  // Llegeix el rol requerit de la ruta (el que hem posat al data: { role: 'ADMIN' })
  const expectedRole = route.data['role']; 

  // REGLA 1: Si l'usuari és ADMIN i va a la seva ruta, el deixem passar
  if (userRole === 'ADMIN') {
    return true;
  }

  // REGLA 2: Si la ruta demana un rol específic (com ADMIN) i no el tenim, fora
  if (expectedRole && userRole !== expectedRole) {
    router.navigate(['/']);
    return false;
  }

  // REGLA 3: Si la ruta no demana rol específic (són les de 'botiga'), demanem ser COMERC
  if (!expectedRole && userRole === 'COMERC') {
    return true;
  }

  // Si res d'això es compleix, enviem l'usuari a l'inici
  router.navigate(['/']);
  return false;
};