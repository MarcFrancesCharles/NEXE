import { Component, OnInit, AfterViewInit, inject, PLATFORM_ID } from '@angular/core';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { RouterLink } from '@angular/router';
import { environment } from '../../../../environments/environment';
import { Auth } from '../../core/services/auth';



@Component({
  selector: 'app-explore',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  templateUrl: './explore.html',
  styleUrl: './explore.css'
})
export class Explore implements OnInit, AfterViewInit {
  private http = inject(HttpClient);
  private auth = inject(Auth);

  // User name for welcome message
  nomUsuari: string = '';

  private getHeaders() {
    return new HttpHeaders({
      'Authorization': `Bearer ${this.auth.obtenirToken()}`
    });
  }

  // Injectem el PLATFORM_ID per saber si som al navegador o al servidor
  private platformId = inject(PLATFORM_ID); 

  comerces: any[] = [];
  filteredComerces: any[] = [];
  categoriesPare: any[] = [];
  subcategoriesActives: any[] = [];

  loading = true;

  searchTerm = '';
  categoriaSeleccionada = '';
  subcategoriaSeleccionada = '';

  map: any;
  markers: any[] = [];
  customIcon: any;
  
  // Guardarem la llibreria de leaflet aquí un cop carregada
  private L: any; 

  ngOnInit() {
    // Load user name for greeting
    this.http.get(`${environment.apiUrl}/perfil-meu`, { headers: this.getHeaders() })
      .subscribe({
        next: (res: any) => this.nomUsuari = res.nom || ''
      });
    this.carregarCategories();
    this.carregarComerces();
    this.carregarCategories();
    this.carregarComerces();
  }

  ngAfterViewInit() {
    if (isPlatformBrowser(this.platformId)) {
      setTimeout(() => {
        this.initMap();
      }, 100);
    }
  }

  // Fem la funció asíncrona per poder fer un import() modern
  async initMap() {
    // @ts-ignore
    this.L = await import('leaflet');

    this.customIcon = this.L.icon({
      iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
      shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
      iconSize: [25, 41],
      iconAnchor: [12, 41],
      popupAnchor: [1, -34]
    });

    this.map = this.L.map('map').setView([41.6167, 0.6222], 14);
    
    this.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors'
    }).addTo(this.map);

    setTimeout(() => {
      this.map.invalidateSize();
    }, 100);

    if (this.filteredComerces.length > 0) {
      this.updateMapMarkers(this.filteredComerces);
    }
  }

  carregarCategories() {
    this.http.get<any[]>(`${environment.apiUrl}/categories`).subscribe({
      next: (data) => this.categoriesPare = data,
      error: (err) => console.error('Error carregant categories', err)
    });
  }

  carregarComerces() {
    this.loading = true;
    this.http.get<any[]>(`${environment.apiUrl}/comerces`).subscribe({
      next: (dades) => {
        this.comerces = dades.map((comerc, index) => {
          let imatgeOriginal = comerc.imatge_url;
          if (imatgeOriginal && !imatgeOriginal.startsWith('http')) {
            imatgeOriginal = `${environment.storageUrl}${imatgeOriginal}`;
          }
          return {
            ...comerc,
            imatge: imatgeOriginal ? imatgeOriginal : this.getShopImage(index)
          };
        });

        this.aplicarFiltres(); 
        this.loading = false;
      },
      error: (err) => {
        console.error('Error carregant comerços', err);
        this.loading = false;
      }
    });
  }

  getShopImage(index: number): string {
    const defaultImages = [
      'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&q=80&w=800', 
      'https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?auto=format&fit=crop&q=80&w=800', 
      'https://images.unsplash.com/photo-1531297172867-4f50efd0481b?auto=format&fit=crop&q=80&w=800', 
      'https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&q=80&w=800'
    ];
    return defaultImages[index % defaultImages.length];
  }

  onCategoriaPareChange() {
    this.subcategoriaSeleccionada = ''; 
    if (this.categoriaSeleccionada) {
      const pareSeleccionat = this.categoriesPare.find(c => c.id_categoria == this.categoriaSeleccionada);
      this.subcategoriesActives = pareSeleccionat ? pareSeleccionat.subcategories : [];
    } else {
      this.subcategoriesActives = [];
    }
    this.aplicarFiltres();
  }

  aplicarFiltres() {
    let result = this.comerces;

    if (this.searchTerm.trim()) {
      const term = this.searchTerm.toLowerCase();
      result = result.filter(c => 
        c.nom_comercial?.toLowerCase().includes(term)
      );
    }

    if (this.categoriaSeleccionada) {
      if (this.subcategoriaSeleccionada) {
        result = result.filter(c => c.id_categoria == this.subcategoriaSeleccionada);
      } else {
        const subIds = this.subcategoriesActives.map(sub => sub.id_categoria);
        result = result.filter(c => subIds.includes(c.id_categoria));
      }
    }

    this.filteredComerces = result;
    this.updateMapMarkers(result);
  }

  updateMapMarkers(comerces: any[]) {
    // Ens assegurem que el mapa i Leaflet estiguin carregats abans de posar xinxetes
    if (!this.map || !this.L) return;

    this.markers.forEach(m => this.map.removeLayer(m));
    this.markers = [];

    comerces.forEach(c => {
      let finalLat = null;
      let finalLng = null;

      // 1. Si té coordenades reals (guardades al nou registre)
      if (c.latitud !== null && c.longitud !== null && c.latitud !== undefined) {
        finalLat = c.latitud;
        finalLng = c.longitud;
      } 
      // 2. Fallback per a botigues antigues o de prova sense coordenades
      else {
        finalLat = 41.6167 + (Math.random() - 0.5) * 0.03;
        finalLng = 0.6222 + (Math.random() - 0.5) * 0.03;
      }

      if (finalLat && finalLng) {
        const marker = this.L.marker([finalLat, finalLng], { icon: this.customIcon })
          .bindPopup(`
            <div style="text-align: center;">
              <b>${c.nom_comercial}</b><br>
              <a href="/shop/${c.id_comerc}" style="display: inline-block; margin-top: 5px; color: #4F46E5; text-decoration: none; font-weight: bold;">Veure botiga</a>
            </div>
          `);
        marker.addTo(this.map);
        this.markers.push(marker);
      }
    });
  }
}