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
  subTabSolicituds: 'comerc' | 'treball' = 'comerc';
  
  stats: any = {};
  usuaris: any[] = [];
  solicituds: any[] = [];
  solicitudsTreball: any[] = [];
  comerces: any[] = [];

  // Filtres de cerca sol·licitats pel manual d'usuari
  cercaUsuari: string = '';
  cercaComerc: string = '';

  ngOnInit() {
    this.loadStats();
    this.loadSolicituds();
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
    this.adminService.getSolicitudsTreball().subscribe(res => this.solicitudsTreball = res);
  }

  loadComerces() {
    this.adminService.getComercos().subscribe(res => this.comerces = res);
  }

  toggleEstat(id: number) {
    this.adminService.toggleEstatUsuari(id).subscribe(() => this.loadUsuaris());
  }

  resoldre(id: number, accio: 'APROVAR' | 'DENEGAR') {
    const missatge = accio === 'APROVAR' 
      ? 'Estàs segur que vols aprovar aquesta sol·licitud?' 
      : 'Estàs segur que vols denegar aquesta sol·licitud?';
    if (confirm(missatge)) {
      this.adminService.resoldreSolicitud(id, accio).subscribe(() => {
        this.loadSolicituds();
        this.loadStats();
      });
    }
  }

  eliminarSolicitudTreball(id: number) {
    if (confirm('Estàs segur que vols eliminar definitivament aquesta sol·licitud?')) {
      this.adminService.eliminarSolicitudTreball(id).subscribe(() => {
        this.loadSolicituds();
      });
    }
  }

  resoldreSolicitudTreball(id: number, accio: 'APROVAR' | 'DENEGAR') {
    const missatge = accio === 'APROVAR'
      ? 'Estàs segur que vols aprovar aquesta sol·licitud de treball? Això actualitzarà el rol de l\'usuari.'
      : 'Estàs segur que vols denegar aquesta sol·licitud de treball?';
    if (confirm(missatge)) {
      this.adminService.resoldreSolicitudTreball(id, accio).subscribe(() => {
        this.loadSolicituds();
        this.loadStats();
      });
    }
  }

  get solicitudsTreballComerc(): any[] {
    return this.solicitudsTreball.filter(s => s.posicio === 'COMERC');
  }

  get solicitudsTreballAdmin(): any[] {
    return this.solicitudsTreball.filter(s => s.posicio === 'ADMIN');
  }

  get totalSolicitudsPendents(): number {
    return this.solicitudsTreball.filter(s => s.estat === 'PENDENT').length;
  }

  get solicitudsComercPendents(): number {
    return this.solicitudsTreball.filter(s => s.posicio === 'COMERC' && s.estat === 'PENDENT').length;
  }

  get solicitudsAdminPendents(): number {
    return this.solicitudsTreball.filter(s => s.posicio === 'ADMIN' && s.estat === 'PENDENT').length;
  }

  filtreEstat: 'tots' | 'actius' | 'bloquejats' = 'tots';
  ordenacio: 'recents' | 'antics' | 'nom-asc' | 'nom-desc' = 'recents';

  // Getters per filtrar en temps real al frontend
  get usuarisFiltrats(): any[] {
    let list = this.usuaris.filter(u => u.rol === 'ESTANDARD');
    
    if (this.filtreEstat === 'actius') {
      list = list.filter(u => u.estat === 'ACTIU');
    } else if (this.filtreEstat === 'bloquejats') {
      list = list.filter(u => u.estat === 'BLOQUEJAT');
    }

    if (this.cercaUsuari.trim()) {
      const query = this.cercaUsuari.toLowerCase();
      list = list.filter(u => 
        u.nom?.toLowerCase().includes(query) || 
        u.correu?.toLowerCase().includes(query)
      );
    }

    // Ordenació: per defecte, els més nous a dalt (comparant created_at o id_usuari)
    list.sort((a, b) => {
      if (this.ordenacio === 'recents') {
        const valA = a.created_at ? new Date(a.created_at).getTime() : (a.id_usuari || 0);
        const valB = b.created_at ? new Date(b.created_at).getTime() : (b.id_usuari || 0);
        return valB - valA;
      } else if (this.ordenacio === 'antics') {
        const valA = a.created_at ? new Date(a.created_at).getTime() : (a.id_usuari || 0);
        const valB = b.created_at ? new Date(b.created_at).getTime() : (b.id_usuari || 0);
        return valA - valB;
      } else if (this.ordenacio === 'nom-asc') {
        return (a.nom || '').localeCompare(b.nom || '');
      } else if (this.ordenacio === 'nom-desc') {
        return (b.nom || '').localeCompare(a.nom || '');
      }
      return 0;
    });

    return list;
  }

  filtreEstatComerc: 'tots' | 'actius' | 'bloquejats' = 'tots';
  ordenacioComerc: 'recents' | 'antics' | 'nom-asc' | 'nom-desc' = 'recents';

  toggleEstatComerc(idUsuari: number) {
    this.adminService.toggleEstatUsuari(idUsuari).subscribe(() => this.loadComerces());
  }

  get comercosFiltrats(): any[] {
    let list = this.comerces;

    // Filtre d'estat (basat en l'estat de l'usuari vinculat al comerç)
    if (this.filtreEstatComerc === 'actius') {
      list = list.filter(c => c.usuari?.estat === 'ACTIU');
    } else if (this.filtreEstatComerc === 'bloquejats') {
      list = list.filter(c => c.usuari?.estat === 'BLOQUEJAT');
    }

    // Filtre per text de cerca
    if (this.cercaComerc.trim()) {
      const query = this.cercaComerc.toLowerCase();
      list = list.filter(c => 
        c.nom_comercial?.toLowerCase().includes(query) || 
        c.cif?.toLowerCase().includes(query) ||
        c.email_contacte?.toLowerCase().includes(query)
      );
    }

    // Ordenació per defecte (més nous primer)
    list.sort((a, b) => {
      if (this.ordenacioComerc === 'recents') {
        const valA = a.created_at ? new Date(a.created_at).getTime() : (a.id_comerc || 0);
        const valB = b.created_at ? new Date(b.created_at).getTime() : (b.id_comerc || 0);
        return valB - valA;
      } else if (this.ordenacioComerc === 'antics') {
        const valA = a.created_at ? new Date(a.created_at).getTime() : (a.id_comerc || 0);
        const valB = b.created_at ? new Date(b.created_at).getTime() : (b.id_comerc || 0);
        return valA - valB;
      } else if (this.ordenacioComerc === 'nom-asc') {
        return (a.nom_comercial || '').localeCompare(b.nom_comercial || '');
      } else if (this.ordenacioComerc === 'nom-desc') {
        return (b.nom_comercial || '').localeCompare(a.nom_comercial || '');
      }
      return 0;
    });

    return list;
  }

  get totalAcumulacions(): number {
    if (!this.stats?.transaccions_per_comerc) return 0;
    return this.stats.transaccions_per_comerc.reduce((acc: number, c: any) => acc + (Number(c.acumulacions) || 0), 0);
  }

  get totalBescanvis(): number {
    if (!this.stats?.transaccions_per_comerc) return 0;
    return this.stats.transaccions_per_comerc.reduce((acc: number, c: any) => acc + (Number(c.bescanvis) || 0), 0);
  }

  get totalTransaccions(): number {
    if (!this.stats?.transaccions_per_comerc) return 0;
    return this.stats.transaccions_per_comerc.reduce((acc: number, c: any) => acc + (Number(c.total) || 0), 0);
  }
}