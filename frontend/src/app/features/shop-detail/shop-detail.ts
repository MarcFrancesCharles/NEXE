import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { ActivatedRoute } from '@angular/router';
import { Auth } from '../../core/services/auth';
import { environment } from '../../../../environments/environment';

@Component({
  selector: 'app-shop-detail',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './shop-detail.html',
  styleUrl: './shop-detail.css'
})
export class ShopDetail implements OnInit {
  storageUrl = environment.storageUrl;

  comerc: any = null;
  ofertes: any[] = [];
  
  // Dades del Client
  socClient: boolean = false;
  elsMeusPunts: number = 0;

  // Variables pel QR de l'oferta
  mostrantQR: boolean = false;
  qrTokenOferta: string = '';
  ofertaSeleccionada: any = null;
  dataCaducitatQR: string = '';

  get qrImageUrl(): string {
    if (!this.qrTokenOferta) return '';
    return `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(this.qrTokenOferta)}`;
  }

  carregant: boolean = true;
  errorMissatge: string = '';

  private http = inject(HttpClient);
  private auth = inject(Auth);
  private route = inject(ActivatedRoute); // Per llegir la URL

  ngOnInit() {
    // 1. Llegim l'ID de la botiga de la URL (ex: /shop/5)
    const idComerc = this.route.snapshot.paramMap.get('id');
    if (idComerc) {
      this.carregarBotiga(idComerc);
    }

    // 2. Comprovem si qui mira això és un client (ESTANDARD) per carregar els seus punts
    if (this.auth.obtenirRol() === 'ESTANDARD') {
      this.socClient = true;
      this.carregarElsMeusPunts();
    }
  }

  private getHeaders() {
    return new HttpHeaders({ 'Authorization': `Bearer ${this.auth.obtenirToken()}` });
  }

  carregarBotiga(id: string) {
    this.http.get(`${environment.apiUrl}/comerces/${id}`).subscribe({
      next: (res: any) => {
        this.comerc = res;
        this.ofertes = res.ofertes || [];
        this.carregant = false;
      },
      error: () => {
        this.errorMissatge = "No s'ha pogut carregar la botiga.";
        this.carregant = false;
      }
    });
  }

  carregarElsMeusPunts() {
    this.http.get(`${environment.apiUrl}/perfil-meu`, { headers: this.getHeaders() })
      .subscribe({
        next: (res: any) => {
          this.elsMeusPunts = res.perfil?.punts_totals || 0;
        }
      });
  }

  // LA MÀGIA: Demanem el QR xifrat al Backend
  generarQrBescanvi(oferta: any) {
    if (this.elsMeusPunts < oferta.cost_punts) return; // Doble seguretat

    const payload = { id_oferta: oferta.id_oferta };

    this.http.post(`${environment.apiUrl}/client/oferta-qr`, payload, { headers: this.getHeaders() })
      .subscribe({
        next: (res: any) => {
          this.ofertaSeleccionada = oferta;
          this.qrTokenOferta = res.qr_token;
          this.dataCaducitatQR = res.caduca_el;
          this.mostrantQR = true;
        },
        error: (err) => {
          alert(err.error.missatge || "Error al generar el codi de l'oferta.");
        }
      });
  }

  tancarQR() {
    this.mostrantQR = false;
    this.qrTokenOferta = '';
    this.ofertaSeleccionada = null;
    
    // Recarreguem els punts per si el botiguer ja l'ha escanejat i ens els han restat!
    if (this.socClient) this.carregarElsMeusPunts();
  }
}