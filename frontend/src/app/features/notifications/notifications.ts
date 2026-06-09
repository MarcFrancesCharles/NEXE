import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { RouterLink } from '@angular/router';
import { Auth } from '../../core/services/auth';
import { environment } from '../../../../environments/environment';

interface Notificacio {
  id: number;
  id_comerc?: number;
  titol: string;
  missatge: string;
  data: Date;
  llegida: boolean;
  icona: string;
  categoria: string;
}

@Component({
  selector: 'app-notifications',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './notifications.html',
  styleUrl: './notifications.css'
})
export class Notifications implements OnInit {
  private auth = inject(Auth);
  private http = inject(HttpClient);

  notificacions: Notificacio[] = [];
  rol: string = '';

  ngOnInit() {
    this.rol = this.auth.obtenirRol() || 'ESTANDARD';
    this.carregarNotificacions();
  }

  private getHeaders() {
    return new HttpHeaders({ 'Authorization': `Bearer ${this.auth.obtenirToken()}` });
  }

  carregarNotificacions() {
    this.http.get<any[]>(`${environment.apiUrl}/notificacions`, { headers: this.getHeaders() })
      .subscribe({
        next: (res) => {
          this.notificacions = res.map(n => ({
            id: n.id_notificacio,
            id_comerc: n.id_comerc,
            titol: n.titol,
            missatge: n.missatge,
            icona: n.icona || '🔔',
            categoria: n.categoria || 'general',
            llegida: !!n.llegida,
            data: new Date(n.created_at)
          }));
        },
        error: (err) => console.error("Error carregant notificacions:", err)
      });
  }

  marcarComALlegida(id: number) {
    this.http.post(`${environment.apiUrl}/notificacions/${id}/llegida`, {}, { headers: this.getHeaders() })
      .subscribe({
        next: () => {
          this.notificacions = this.notificacions.map(n => {
            if (n.id === id) {
              return { ...n, llegida: true };
            }
            return n;
          });
        },
        error: (err) => console.error("Error al marcar com a llegida:", err)
      });
  }

  marcarTotesLlegides() {
    this.http.post(`${environment.apiUrl}/notificacions/llegides-totes`, {}, { headers: this.getHeaders() })
      .subscribe({
        next: () => {
          this.notificacions = this.notificacions.map(n => ({ ...n, llegida: true }));
        },
        error: (err) => console.error("Error al marcar totes com a llegides:", err)
      });
  }

  eliminarNotificacio(id: number) {
    this.http.delete(`${environment.apiUrl}/notificacions/${id}`, { headers: this.getHeaders() })
      .subscribe({
        next: () => {
          this.notificacions = this.notificacions.filter(n => n.id !== id);
        },
        error: (err) => console.error("Error al eliminar notificació:", err)
      });
  }

  get totalNoLlegides(): number {
    return this.notificacions.filter(n => !n.llegida).length;
  }
}
