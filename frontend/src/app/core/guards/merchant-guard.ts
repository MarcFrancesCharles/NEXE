// src/app/core/guards/merchant-guard.ts
import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { Auth } from '../services/auth';

/**
 * Guard that prevents merchant (COMERC) users from accessing the Explore page.
 * If the user role is COMERC, they are redirected to the merchant dashboard
 * ('/la-meva-botiga'). All other roles (ADMIN, normal users) may proceed.
 */
export const merchantGuard: CanActivateFn = (route, state) => {
  const router = inject(Router);
  const auth = inject(Auth);
  const role = auth.obtenirRol();

  if (role === 'COMERC') {
    // Redirect merchants to their shop dashboard
    router.navigate(['/la-meva-botiga']);
    return false;
  }
  // Allow everyone else (including ADMIN and normal users)
  return true;
};
