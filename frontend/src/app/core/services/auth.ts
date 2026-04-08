import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../../environments/environment';


@Injectable({
  providedIn: 'root'
})
export class Auth {
  private apiUrl = environment.apiUrl;

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

  getCategories(): Observable<any[]> {
    return this.http.get<any[]>(`${this.apiUrl}/categories`);
  }

  getElMeuComerc(): Observable<any> {
    const headers = new HttpHeaders({ 'Authorization': `Bearer ${this.obtenirToken()}` });
    return this.http.get<any>(`${this.apiUrl}/el-meu-comerc`, { headers });
  }

  actualitzarComerc(dades: FormData): Observable<any> {
    const headers = new HttpHeaders({ 'Authorization': `Bearer ${this.obtenirToken()}` });
    return this.http.post<any>(`${this.apiUrl}/el-meu-comerc`, dades, { headers });
  }

  obtenirCarnetQr(): Observable<any> {
    const headers = new HttpHeaders({ 'Authorization': `Bearer ${this.obtenirToken()}` });
    return this.http.get<any>(`${this.apiUrl}/client/carnet-qr`, { headers });
  }

  logout() {
    localStorage.removeItem('nexe_token');
    localStorage.removeItem('nexe_rol');
  }
}