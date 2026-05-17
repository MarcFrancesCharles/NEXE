import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '../../../../environments/environment'; // <- Ruta corregida (4 nivells)
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AdminService {
  private http = inject(HttpClient);
  private apiUrl = `${environment.apiUrl}/admin`;

  // Funció auxiliar per generar les capçaleres amb el token
  private getHeaders(): { headers: HttpHeaders } {
    const token = localStorage.getItem('nexe_token');
    return {
      headers: new HttpHeaders({ 'Authorization': `Bearer ${token}` })
    };
  }

  getStats(): Observable<any> {
    return this.http.get(`${this.apiUrl}/stats`, this.getHeaders());
  }

  getUsuaris(): Observable<any[]> {
    return this.http.get<any[]>(`${this.apiUrl}/usuaris`, this.getHeaders());
  }

  toggleEstatUsuari(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/usuaris/${id}/estat`, {}, this.getHeaders());
  }

  getSolicituds(): Observable<any[]> {
    return this.http.get<any[]>(`${this.apiUrl}/solicituds`, this.getHeaders());
  }

  resoldreSolicitud(id: number, accio: 'APROVAR' | 'DENEGAR'): Observable<any> {
    return this.http.post(`${this.apiUrl}/solicituds/${id}/resoldre`, { accio }, this.getHeaders());
  }

  getComercos(): Observable<any[]> {
    return this.http.get<any[]>(`${this.apiUrl}/comerces`, this.getHeaders());
  }
}