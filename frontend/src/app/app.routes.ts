import { Routes } from '@angular/router';
import { Explore } from './features/explore/explore';
import { Login } from './auth/login/login';
import { Register } from './auth/register/register';
import { Shop } from './features/shop/shop';
import { Profile } from './features/profile/profile';
import { CreateOffer } from './features/shop/create-offer/create-offer';
import { authGuard } from './core/guards/auth-guard';
import { roleGuard } from './core/guards/role-guard';
import { ShopDetail } from './features/shop-detail/shop-detail';
import { ShopRequest } from './features/shop-request/shop-request';
import { About } from './features/about/about';
import { Legal } from './features/legal/legal';
import { Faq } from './features/faq/faq';
import { Careers } from './features/careers/careers';

// 1. IMPORTACIÓ DEL NOU COMPONENT ADMIN
import { AdminDashboard } from './features/admin-dashboard/admin-dashboard';

export const routes: Routes = [
  { path: '', component: Explore },
  { path: 'login', component: Login },
  { path: 'register', component: Register },
  { path: 'perfil', component: Profile, canActivate: [authGuard] },
  { path: 'solicitar-comerc', component: ShopRequest, canActivate: [authGuard] },
  { path: 'botiga', component: Shop, canActivate: [authGuard, roleGuard] },
  { path: 'la-meva-botiga', component: Shop, canActivate: [authGuard, roleGuard] },
  { path: 'la-meva-botiga/crear-oferta', component: CreateOffer, canActivate: [authGuard, roleGuard] },
  { path: 'shop/:id', component: ShopDetail },
  
  // Rutes del Footer
  { path: 'sobre-nosaltres', component: About },
  { path: 'legal', component: Legal },
  { path: 'faq', component: Faq },
  { path: 'treballa-amb-nosaltres', component: Careers },
  
  // 2. LA RUTA D'ADMIN: Va protegida i amb la propietat data
  { 
    path: 'admin', 
    component: AdminDashboard, 
    canActivate: [authGuard, roleGuard], 
    data: { role: 'ADMIN' } 
  },
  
  // 3. RUTA COMODÍ (SEMPRE HA D'ANAR AL FINAL DE TOT)
  { path: '**', redirectTo: '' }
];