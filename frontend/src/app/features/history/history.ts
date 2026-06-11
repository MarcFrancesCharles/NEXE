import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { RouterLink } from '@angular/router';
import { Auth } from '../../core/services/auth';
import { environment } from '../../../../environments/environment';

interface Oferta {
  titol: string;
  descripcio: string;
  cost_punts: number;
  imatge?: string;
}

interface Comerc {
  id_comerc: number;
  nom_comercial: string;
}

interface Transaccio {
  id_transaccio: number;
  tipus: string;
  punts_mov: number;
  data_hora: Date;
  oferta: Oferta | null;
  comerc: Comerc;
}

@Component({
  selector: 'app-history',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './history.html',
  styleUrl: './history.css'
})
export class History implements OnInit {
  private auth = inject(Auth);
  private http = inject(HttpClient);

  bescanvis: Transaccio[] = [];
  carregant: boolean = true;

  ngOnInit() {
    this.carregarHistorial();
  }

  private getHeaders() {
    return new HttpHeaders({ 'Authorization': `Bearer ${this.auth.obtenirToken()}` });
  }

  carregarHistorial() {
    this.carregant = true;
    this.http.get<any>(`${environment.apiUrl}/perfil-meu`, { headers: this.getHeaders() })
      .subscribe({
        next: (res) => {
          if (res && res.transaccions) {
            // Filtrem transaccions de tipus BESCANVI (ofertes canjeades) o ACUMULACIO (punts guanyats)
            this.bescanvis = res.transaccions
              .filter((t: any) => t.tipus === 'BESCANVI' || t.tipus === 'ACUMULACIO')
              .map((t: any) => ({
                id_transaccio: t.id_transaccio,
                tipus: t.tipus,
                punts_mov: t.punts_mov,
                data_hora: new Date(t.data_hora),
                oferta: t.oferta || null,
                comerc: t.comerc
              }));
          }
          this.carregant = false;
        },
        error: (err) => {
          console.error("Error carregant l'historial:", err);
          this.carregant = false;
        }
      });
  }
}
