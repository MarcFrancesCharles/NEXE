import { Routes } from '@angular/router';
import { Explore } from './features/explore/explore';
import { Login } from './auth/login/login';
import { Register } from './auth/register/register';
import { Shop } from './features/shop/shop';
import { Profile } from './features/profile/profile';
import { CreateOffer } from './features/shop/create-offer/create-offer';
import { authGuard } from './core/guards/auth-guard';
import { roleGuard } from './core/guards/role-guard';

export const routes: Routes = [
  { path: '', component: Explore },      // Pàgina principal (Llista de botigues)
  { path: 'login', component: Login },     // Pantalla de Login
  { path: 'register', component: Register },  // Pantalla de Registre          
  { path: 'perfil', component: Profile, canActivate: [authGuard] },
  { path: 'botiga', component: Shop, canActivate: [authGuard, roleGuard] },
  { path: 'la-meva-botiga', component: Shop, canActivate: [authGuard, roleGuard] }, 
  { path: 'la-meva-botiga/crear-oferta', component: CreateOffer, canActivate: [authGuard, roleGuard] },
  { path: '**', redirectTo: '' } // Si s'equivoca de URL, l'enviem a Inici
];