import { Component, OnInit, AfterViewInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpClient } from '@angular/common/http';
import { RouterLink } from '@angular/router';
import * as L from 'leaflet';

@Component({
  selector: 'app-explore',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  templateUrl: './explore.html',
  styleUrl: './explore.css'
})
export class Explore implements OnInit, AfterViewInit {
  private http = inject(HttpClient);

  // Dades
  comerces: any[] = [];
  filteredComerces: any[] = [];
  categoriesPare: any[] = [];
  subcategoriesActives: any[] = [];

  loading = true;

  // Filtres
  searchTerm = '';
  categoriaSeleccionada = '';
  subcategoriaSeleccionada = '';

  // Mapa
  map: any;
  markers: L.Marker[] = [];
  
  // Icona personalitzada per a Leaflet
  customIcon = L.icon({
    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34]
  });

  ngOnInit() {
    this.carregarCategories();
    this.carregarComerces();
  }

  ngAfterViewInit() {
    this.initMap();
  }

  initMap() {
    // Centrem el mapa a Lleida
    this.map = L.map('map').setView([41.6167, 0.6222], 14);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors'
    }).addTo(this.map);
  }

  carregarCategories() {
    this.http.get<any[]>('http://localhost:8000/api/categories').subscribe(data => {
      this.categoriesPare = data;
    });
  }

  carregarComerces() {
    this.loading = true;
    this.http.get<any[]>('http://localhost:8000/api/comerces').subscribe({
      next: (dades) => {
        // Recuperem la teva màgia per a les imatges!
        this.comerces = dades.map((comerc, index) => {
          let imatgeOriginal = comerc.imatge_url;
          if (imatgeOriginal && !imatgeOriginal.startsWith('http')) {
            imatgeOriginal = `http://localhost:8000/storage/${imatgeOriginal}`;
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

  // Helper method que tenies per a imatges per defecte
  getShopImage(index: number): string {
    const defaultImages = [
      'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&q=80&w=800', 
      'https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?auto=format&fit=crop&q=80&w=800', 
      'https://images.unsplash.com/photo-1531297172867-4f50efd0481b?auto=format&fit=crop&q=80&w=800', 
      'https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&q=80&w=800', 
      'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&q=80&w=800', 
      'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&q=80&w=800', 
      'https://images.unsplash.com/photo-1576602976047-174e57a47881?auto=format&fit=crop&q=80&w=800', 
      'https://images.unsplash.com/photo-1472851294608-062f824d29cc?auto=format&fit=crop&q=80&w=800', 
    ];
    return defaultImages[index % defaultImages.length];
  }

  onCategoriaPareChange() {
    this.subcategoriaSeleccionada = ''; // Reset de la subcategoria
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

    // 1. Filtre de text (Cerca lliure per nom o adreça)
    if (this.searchTerm.trim()) {
      const term = this.searchTerm.toLowerCase();
      result = result.filter(c => 
        c.nom_comercial?.toLowerCase().includes(term) || 
        c.direccio?.toLowerCase().includes(term)
      );
    }

    // 2. Filtre de Categories
    if (this.categoriaSeleccionada) {
      if (this.subcategoriaSeleccionada) {
        // Filtrar exactament per la subcategoria triada
        result = result.filter(c => c.id_categoria == this.subcategoriaSeleccionada);
      } else {
        // Mostrar tots els comerços que pertanyin a qualsevol subcategoria del Pare
        const subIds = this.subcategoriesActives.map(sub => sub.id_categoria);
        result = result.filter(c => subIds.includes(c.id_categoria));
      }
    }

    this.filteredComerces = result;
    this.updateMapMarkers(result);
  }

  async updateMapMarkers(comerces: any[]) {
    // 1. Esborrar xinxetes antigues
    this.markers.forEach(m => this.map.removeLayer(m));
    this.markers = [];

    // 2. Crear noves xinxetes
    for (const c of comerces) {
      let lat = null;
      let lng = null;

      // Si ja tenim coordenades guardades
      if (c.coord_gps) {
        const parts = c.coord_gps.split(',');
        lat = parseFloat(parts[0]);
        lng = parseFloat(parts[1]);
      } 
      // Si no tenim coords, fem "màgia" i les busquem a OpenStreetMap a partir de l'adreça!
      else if (c.direccio) {
        try {
          const query = encodeURIComponent(`${c.direccio}, Lleida, Espanya`);
          const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${query}`);
          const data = await res.json();
          if (data && data.length > 0) {
            lat = parseFloat(data[0].lat);
            lng = parseFloat(data[0].lon);
          }
        } catch (e) {
          console.error('Error buscant coordenades per:', c.nom_comercial);
        }
      }

      // Si hem aconseguit Lat i Lng, posem el marcador
      if (lat && lng) {
        const marker = L.marker([lat, lng], { icon: this.customIcon })
          .bindPopup(`
            <div style="text-align: center;">
              <b>${c.nom_comercial}</b><br>
              <span style="color: #666; font-size: 0.9em;">${c.direccio || 'Lleida'}</span><br>
              <a href="/shop/${c.id_comerc}" style="display: inline-block; margin-top: 5px; color: #4F46E5; text-decoration: none; font-weight: bold;">Veure botiga</a>
            </div>
          `);
        marker.addTo(this.map);
        this.markers.push(marker);
      }
    }
  }
}