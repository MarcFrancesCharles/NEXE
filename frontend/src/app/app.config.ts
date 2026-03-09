import { ApplicationConfig } from '@angular/core';
import { provideRouter } from '@angular/router';
import { routes } from './app.routes';
// Importem el proveïdor de peticions HTTP
import { provideHttpClient, withFetch } from '@angular/common/http'; 

export const appConfig: ApplicationConfig = {
  providers: [
    provideRouter(routes),
    // L'afegim aquí per tenir-lo disponible a tota l'App
    provideHttpClient(withFetch()) 
  ]
};