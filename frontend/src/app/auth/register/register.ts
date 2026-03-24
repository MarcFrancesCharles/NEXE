import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { Auth } from '../../core/services/auth';
import { Subject, of } from 'rxjs';
import { debounceTime, distinctUntilChanged, switchMap, catchError } from 'rxjs/operators';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterLink],
  templateUrl: './register.html',
  styleUrl: './register.css'
})
export class Register implements OnInit {
  regForm: FormGroup;
  loading = false;
  errorMsg = '';
  
  categoriesPare: any[] = [];
  subcategoriesActives: any[] = [];

  // Variables pel cercador d'adreces tipus Google Maps
  adrecaSubject = new Subject<string>();
  adrecesSugerides: any[] = [];
  buscantAdreca = false;

  private fb = inject(FormBuilder);
  private auth = inject(Auth);
  private router = inject(Router);
  private http = inject(HttpClient);

  constructor() {
    // Regex professional per DNI / NIE / CIF
    const nifRegex = /^[0-9A-Z][0-9]{7}[0-9A-Z]$/i;

    this.regForm = this.fb.group({
      nom: ['', [Validators.required, Validators.minLength(2)]],
      correu: ['', [Validators.required, Validators.email]],
      contrasenya: ['', [Validators.required, Validators.minLength(8)]],
      rol: ['ESTANDARD', [Validators.required]],
      
      // Camps del comerç
      id_sector: [''], 
      id_categoria: [''],
      cif: ['', [Validators.pattern(nifRegex)]], 
      direccio: [''] 
    });

    // Validacions dinàmiques
    this.regForm.get('rol')?.valueChanges.subscribe(rol => {
      const secCtrl = this.regForm.get('id_sector');
      const catCtrl = this.regForm.get('id_categoria');
      const cifCtrl = this.regForm.get('cif');
      const dirCtrl = this.regForm.get('direccio');

      if (rol === 'COMERC') {
        secCtrl?.setValidators([Validators.required]);
        catCtrl?.setValidators([Validators.required]);
        cifCtrl?.addValidators([Validators.required]);
        dirCtrl?.setValidators([Validators.required]);
      } else {
        secCtrl?.clearValidators();
        catCtrl?.clearValidators();
        cifCtrl?.removeValidators([Validators.required]);
        dirCtrl?.clearValidators();
      }
      secCtrl?.updateValueAndValidity();
      catCtrl?.updateValueAndValidity();
      cifCtrl?.updateValueAndValidity();
      dirCtrl?.updateValueAndValidity();
    });

    // Detectar canvi de Sector per mostrar les Especialitats
    this.regForm.get('id_sector')?.valueChanges.subscribe(pareId => {
      console.log('Sector seleccionat:', pareId);
      // Usem == i no === per si el pareId ve com a string del HTML
      const pareSeleccionat = this.categoriesPare.find(c => c.id_categoria == pareId);
      this.subcategoriesActives = pareSeleccionat ? pareSeleccionat.subcategories : [];
      this.regForm.get('id_categoria')?.setValue(''); // Resetejem especialitat
    });
  }

  ngOnInit() {
    this.carregarCategories();
    this.configurarAutocompletarAdreca();
  }

  carregarCategories() {
    this.http.get<any[]>('http://localhost:8000/api/categories').subscribe({
      next: (data) => {
        console.log('Categories carregades del backend:', data);
        this.categoriesPare = data;
      },
      error: (err) => {
        console.error('ERROR: No s\'han pogut carregar les categories.', err);
      }
    });
  }

  // --- MÀGIA DE L'AUTOCOMPLETAR ADRECES ---
  configurarAutocompletarAdreca() {
    this.adrecaSubject.pipe(
      debounceTime(400),
      distinctUntilChanged(),
      switchMap(query => {
        if (!query || query.length < 4) {
          this.adrecesSugerides = [];
          return of([]);
        }
        this.buscantAdreca = true;
        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query + ', Lleida, Espanya')}&limit=4`;
        return this.http.get<any[]>(url).pipe(catchError(() => of([])));
      })
    ).subscribe(resultats => {
      this.buscantAdreca = false;
      this.adrecesSugerides = resultats;
    });
  }

  buscarAdreca(event: any) {
    this.adrecaSubject.next(event.target.value);
  }

  seleccionarAdreca(adreca: any) {
    // Ens quedem només amb el carrer sense tanta palla
    const nomNet = adreca.display_name.split(', Lleida')[0] + ', Lleida';
    this.regForm.get('direccio')?.setValue(nomNet);
    this.adrecesSugerides = []; 
  }

  enviar() {
    if (this.regForm.invalid) {
      // Marquem tots els camps com a tocats perquè surtin els errors en vermell
      this.regForm.markAllAsTouched();
      return;
    }

    this.loading = true;
    this.errorMsg = '';

    // Eliminem id_sector perquè a la BD només guardem id_categoria (l'especialitat)
    const dadesEnviament = { ...this.regForm.value };
    delete dadesEnviament.id_sector;

    this.auth.register(dadesEnviament).subscribe({
      next: () => {
        this.loading = false;
        this.router.navigate(['/login'], { queryParams: { registered: 'true' }});
      },
      error: (err) => {
        this.loading = false;
        this.errorMsg = err.error?.message || 'Error en el registre. Revisa les dades.';
      }
    });
  }
}