import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AdminService } from '../../core/services/admin';

@Component({
  selector: 'app-admin-dashboard',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './admin-dashboard.html',
  styleUrls: ['./admin-dashboard.css']
})
export class AdminDashboard implements OnInit {
  private adminService = inject(AdminService);

  activeTab: 'stats' | 'usuaris' | 'solicituds' | 'comerces' = 'stats';
  
  stats: any = {};
  usuaris: any[] = [];
  solicituds: any[] = [];
  comerces: any[] = [];

  // Filtres de cerca sol·licitats pel manual d'usuari
  cercaUsuari: string = '';
  cercaComerc: string = '';

  ngOnInit() {
    this.loadStats();
  }

  setTab(tab: 'stats' | 'usuaris' | 'solicituds' | 'comerces') {
    this.activeTab = tab;
    if (tab === 'stats') this.loadStats();
    if (tab === 'usuaris') this.loadUsuaris();
    if (tab === 'solicituds') this.loadSolicituds();
    if (tab === 'comerces') this.loadComerces();
  }

  loadStats() {
    this.adminService.getStats().subscribe(res => this.stats = res);
  }

  loadUsuaris() {
    this.adminService.getUsuaris().subscribe(res => this.usuaris = res);
  }

  loadSolicituds() {
    this.adminService.getSolicituds().subscribe(res => this.solicituds = res);
  }

  loadComerces() {
    this.adminService.getComercos().subscribe(res => this.comerces = res);
  }

  toggleEstat(id: number) {
    this.adminService.toggleEstatUsuari(id).subscribe(() => this.loadUsuaris());
  }

  resoldre(id: number, accio: 'APROVAR' | 'DENEGAR') {
    this.adminService.resoldreSolicitud(id, accio).subscribe(() => {
      this.loadSolicituds();
      this.loadStats();
    });
  }

  // Getters per filtrar en temps real al frontend
  get usuarisFiltrats(): any[] {
    if (!this.cercaUsuari.trim()) return this.usuaris;
    const query = this.cercaUsuari.toLowerCase();
    return this.usuaris.filter(u => 
      u.nom?.toLowerCase().includes(query) || 
      u.correu?.toLowerCase().includes(query)
    );
  }

  get comercosFiltrats(): any[] {
    if (!this.cercaComerc.trim()) return this.comerces;
    const query = this.cercaComerc.toLowerCase();
    return this.comerces.filter(c => 
      c.nom_comercial?.toLowerCase().includes(query) || 
      c.cif?.toLowerCase().includes(query) ||
      c.email_contacte?.toLowerCase().includes(query)
    );
  }
}