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
        this.comerces = dades;
        this.loading = false;
      },
      error: (err) => {
        console.error('Error carregant comerços', err);
        this.loading = false;
      }
    });
  }
}