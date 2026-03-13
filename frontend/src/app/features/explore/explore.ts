import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-explore',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './explore.html',
  styleUrl: './explore.css'
})
export class Explore implements OnInit {
  comerces: any[] = [];
  loading = true;
  private http = inject(HttpClient);

  ngOnInit() {
    this.carregarComerces();
  }

  carregarComerces() {
    this.http.get<any[]>('http://localhost:8000/api/comerces').subscribe({
      next: (dades) => {
        this.comerces = dades.map((comerc, index) => {
          let imatgeOriginal = comerc.imatge_url;
          // Si ve de la BD (té path), hi posem el prefix de storage
          if (imatgeOriginal && !imatgeOriginal.startsWith('http')) {
            imatgeOriginal = `http://localhost:8000/storage/${imatgeOriginal}`;
          }

          return {
            ...comerc,
            imatge: imatgeOriginal ? imatgeOriginal : this.getShopImage(index)
          };
        });
        this.loading = false;
      },
      error: (err) => {
        console.error('Error carregant comerços', err);
        this.loading = false;
      }
    });
  }

  // Helper method to provide premium placeholder images based on shop index
  getShopImage(index: number): string {
    const defaultImages = [
      'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&q=80&w=800', // Restaurant / Cafe
      'https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?auto=format&fit=crop&q=80&w=800', // Clothing
      'https://images.unsplash.com/photo-1531297172867-4f50efd0481b?auto=format&fit=crop&q=80&w=800', // Tech
      'https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&q=80&w=800', // Books
      'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&q=80&w=800', // Gym / Sports
      'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&q=80&w=800', // Supermarket / Food
      'https://images.unsplash.com/photo-1576602976047-174e57a47881?auto=format&fit=crop&q=80&w=800', // Pharmacy / Health
      'https://images.unsplash.com/photo-1472851294608-062f824d29cc?auto=format&fit=crop&q=80&w=800', // Retail Store
    ];
    return defaultImages[index % defaultImages.length];
  }
}