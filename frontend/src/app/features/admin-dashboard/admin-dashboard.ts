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
    const count = this.solicitudsTreball.filter(s => s.estat === 'PENDENT').length;
    return count >= 2 ? count - 2 : 0;
  }

  get solicitudsComercPendents(): number {
    return this.solicitudsTreball.filter(s => s.posicio === 'COMERC' && s.estat === 'PENDENT').length;
  }

  get solicitudsAdminPendents(): number {
    return this.solicitudsTreball.filter(s => s.posicio === 'ADMIN' && s.estat === 'PENDENT').length;
  }

  filtreEstat: 'tots' | 'actius' | 'bloquejats' = 'tots';
  ordenacio: 'recents' | 'antics' | 'nom-asc' | 'nom-desc' | '' = 'recents';

  sortFieldUsuari: string = 'created_at';
  sortAscUsuari: boolean = false;

  sortByUsuari(field: string) {
    if (this.sortFieldUsuari === field) {
      this.sortAscUsuari = !this.sortAscUsuari;
    } else {
      this.sortFieldUsuari = field;
      this.sortAscUsuari = true;
    }
    this.syncDropdownUsuari();
  }

  onOrdenacioChange(val: string) {
    if (val === 'recents') {
      this.sortFieldUsuari = 'created_at';
      this.sortAscUsuari = false;
    } else if (val === 'antics') {
      this.sortFieldUsuari = 'created_at';
      this.sortAscUsuari = true;
    } else if (val === 'nom-asc') {
      this.sortFieldUsuari = 'nom';
      this.sortAscUsuari = true;
    } else if (val === 'nom-desc') {
      this.sortFieldUsuari = 'nom';
      this.sortAscUsuari = false;
    }
  }

  private syncDropdownUsuari() {
    if (this.sortFieldUsuari === 'created_at' && !this.sortAscUsuari) {
      this.ordenacio = 'recents';
    } else if (this.sortFieldUsuari === 'created_at' && this.sortAscUsuari) {
      this.ordenacio = 'antics';
    } else if (this.sortFieldUsuari === 'nom' && this.sortAscUsuari) {
      this.ordenacio = 'nom-asc';
    } else if (this.sortFieldUsuari === 'nom' && !this.sortAscUsuari) {
      this.ordenacio = 'nom-desc';
    } else {
      this.ordenacio = '';
    }
  }

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

    // Ordenació per columnes/general
    list.sort((a, b) => {
      let valA: any;
      let valB: any;

      if (this.sortFieldUsuari === 'id_usuari') {
        valA = a.id_usuari || 0;
        valB = b.id_usuari || 0;
      } else if (this.sortFieldUsuari === 'nom') {
        valA = a.nom || '';
        valB = b.nom || '';
      } else if (this.sortFieldUsuari === 'correu') {
        valA = a.correu || '';
        valB = b.correu || '';
      } else if (this.sortFieldUsuari === 'punts') {
        valA = a.perfil?.punts_totals || 0;
        valB = b.perfil?.punts_totals || 0;
      } else if (this.sortFieldUsuari === 'created_at') {
        valA = a.created_at ? new Date(a.created_at).getTime() : (a.id_usuari || 0);
        valB = b.created_at ? new Date(b.created_at).getTime() : (b.id_usuari || 0);
      } else if (this.sortFieldUsuari === 'estat') {
        valA = a.estat || '';
        valB = b.estat || '';
      } else {
        return 0;
      }

      if (typeof valA === 'string' && typeof valB === 'string') {
        return this.sortAscUsuari ? valA.localeCompare(valB) : valB.localeCompare(valA);
      } else {
        return this.sortAscUsuari ? (valA - valB) : (valB - valA);
      }
    });

    return list;
  }

  filtreEstatComerc: 'tots' | 'actius' | 'bloquejats' = 'tots';
  ordenacioComerc: 'recents' | 'antics' | 'nom-asc' | 'nom-desc' | '' = 'recents';

  sortFieldComerc: string = 'created_at';
  sortAscComerc: boolean = false;

  sortByComerc(field: string) {
    if (this.sortFieldComerc === field) {
      this.sortAscComerc = !this.sortAscComerc;
    } else {
      this.sortFieldComerc = field;
      this.sortAscComerc = true;
    }
    this.syncDropdownComerc();
  }

  onOrdenacioComercChange(val: string) {
    if (val === 'recents') {
      this.sortFieldComerc = 'created_at';
      this.sortAscComerc = false;
    } else if (val === 'antics') {
      this.sortFieldComerc = 'created_at';
      this.sortAscComerc = true;
    } else if (val === 'nom-asc') {
      this.sortFieldComerc = 'nom_comercial';
      this.sortAscComerc = true;
    } else if (val === 'nom-desc') {
      this.sortFieldComerc = 'nom_comercial';
      this.sortAscComerc = false;
    }
  }

  private syncDropdownComerc() {
    if (this.sortFieldComerc === 'created_at' && !this.sortAscComerc) {
      this.ordenacioComerc = 'recents';
    } else if (this.sortFieldComerc === 'created_at' && this.sortAscComerc) {
      this.ordenacioComerc = 'antics';
    } else if (this.sortFieldComerc === 'nom_comercial' && this.sortAscComerc) {
      this.ordenacioComerc = 'nom-asc';
    } else if (this.sortFieldComerc === 'nom_comercial' && !this.sortAscComerc) {
      this.ordenacioComerc = 'nom-desc';
    } else {
      this.ordenacioComerc = '';
    }
  }

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

    // Ordenació per columnes/general
    list.sort((a, b) => {
      let valA: any;
      let valB: any;

      if (this.sortFieldComerc === 'id_comerc') {
        valA = a.id_comerc || 0;
        valB = b.id_comerc || 0;
      } else if (this.sortFieldComerc === 'nom_comercial') {
        valA = a.nom_comercial || '';
        valB = b.nom_comercial || '';
      } else if (this.sortFieldComerc === 'cif') {
        valA = a.cif || '';
        valB = b.cif || '';
      } else if (this.sortFieldComerc === 'email_contacte') {
        valA = a.email_contacte || '';
        valB = b.email_contacte || '';
      } else if (this.sortFieldComerc === 'created_at') {
        valA = a.created_at ? new Date(a.created_at).getTime() : (a.id_comerc || 0);
        valB = b.created_at ? new Date(b.created_at).getTime() : (b.id_comerc || 0);
      } else if (this.sortFieldComerc === 'estat') {
        valA = a.usuari?.estat || 'ACTIU';
        valB = b.usuari?.estat || 'ACTIU';
      } else {
        return 0;
      }

      if (typeof valA === 'string' && typeof valB === 'string') {
        return this.sortAscComerc ? valA.localeCompare(valB) : valB.localeCompare(valA);
      } else {
        return this.sortAscComerc ? (valA - valB) : (valB - valA);
      }
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