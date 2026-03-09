import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class Auth {
  private apiUrl = 'http://localhost:8000/api'; 

  constructor(private http: HttpClient) { }

  login(dades: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/login`, dades);
  }

  register(dades: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/register`, dades);
  }

  guardarToken(token: string, rol: string) {
    localStorage.setItem('nexe_token', token);
    localStorage.setItem('nexe_rol', rol);
    
  }

  obtenirToken() {
    return localStorage.getItem('nexe_token');
  }

  obtenirRol(): string | null {
    return localStorage.getItem('nexe_rol');
  }

  logout() {
    localStorage.removeItem('nexe_token');
    localStorage.removeItem('nexe_rol');
  }
}