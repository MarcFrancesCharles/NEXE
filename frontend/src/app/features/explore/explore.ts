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

  comerces: any[] = [];
  filteredComerces: any[] = [];
  categoriesPare: any[] = [];
  subcategoriesActives: any[] = [];

  loading = true;

  searchTerm = '';
  categoriaSeleccionada = '';
  subcategoriaSeleccionada = '';

  map: any;
  markers: L.Marker[] = [];
  
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
    this.map = L.map('map').setView([41.6167, 0.6222], 14);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
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
    this.http.get<any[]>('http://localhost:8000/api/categories').subscribe(data => {
      this.categoriesPare = data;
    });
  }

  carregarComerces() {
    this.loading = true;
    this.http.get<any[]>('http://localhost:8000/api/comerces').subscribe({
      next: (dades) => {
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
        c.nom_comercial?.toLowerCase().includes(term) || 
        c.direccio?.toLowerCase().includes(term)
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

  // NOVA FUNCIÓ INSTANTÀNIA I A PROVA D'ERRORS
  updateMapMarkers(comerces: any[]) {
    if (!this.map) return;
    
    this.markers.forEach(m => this.map.removeLayer(m));
    this.markers = [];

    comerces.forEach(c => {
      let lat = null;
      let lng = null;

      // 1. Si té coordenades reals (guardades al nou registre)
      if (c.coord_gps) {
        const parts = c.coord_gps.split(',');
        lat = parseFloat(parts[0]);
        lng = parseFloat(parts[1]);
      } 
      // 2. Fallback per a botigues antigues sense coordenades!
      else {
        // Assignem una ubicació aleatòria a Lleida perquè es vegin
        lat = 41.6167 + (Math.random() - 0.5) * 0.03;
        lng = 0.6222 + (Math.random() - 0.5) * 0.03;
      }

      if (lat && lng) {
        const marker = L.marker([lat, lng], { icon: this.customIcon })
          .bindPopup(`
            <div style="text-align: center;">
              <b>${c.nom_comercial}</b><br>
              <span style="color: #666; font-size: 0.9em;">${c.direccio || 'Sense Adreça'}</span><br>
              <a href="/shop/${c.id_comerc}" style="display: inline-block; margin-top: 5px; color: #4F46E5; text-decoration: none; font-weight: bold;">Veure botiga</a>
            </div>
          `);
        marker.addTo(this.map);
        this.markers.push(marker);
      }
    });
  }
}