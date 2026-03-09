import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { FormsModule } from '@angular/forms';
import { Auth } from '../../core/services/auth';

@Component({
  selector: 'app-shop',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './shop.html',
  styleUrl: './shop.css'
})
export class Shop implements OnInit {
  botiga: any = null;
  carregant = true;
  
  private http = inject(HttpClient);
  private auth = inject(Auth);

  ngOnInit() {
    this.carregarDadesBotiga();
  }

  private getHeaders() {
    return new HttpHeaders({ 'Authorization': `Bearer ${this.auth.obtenirToken()}` });
  }

  carregarDadesBotiga() {
    // Suposant que a Laravel tens una ruta per veure la teva pròpia botiga
    this.http.get('http://localhost:8000/api/la-meva-botiga', { headers: this.getHeaders() })
      .subscribe({
        next: (res) => {
          this.botiga = res;
          this.carregant = false;
        },
        error: (err) => {
          console.error(err);
          this.carregant = false;
        }
      });
  }
}