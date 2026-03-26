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

  adrecaSubject = new Subject<string>();
  adrecesSugerides: any[] = [];
  buscantAdreca = false;

  private fb = inject(FormBuilder);
  private auth = inject(Auth);
  private router = inject(Router);
  private http = inject(HttpClient);

  constructor() {
    const nifRegex = /^[0-9A-Z][0-9]{7}[0-9A-Z]$/i;

    this.regForm = this.fb.group({
      nom: ['', [Validators.required, Validators.minLength(2)]],
      correu: ['', [Validators.required, Validators.email]],
      contrasenya: ['', [Validators.required, Validators.minLength(8)]],
      rol: ['ESTANDARD', [Validators.required]],
      
      id_sector: [''], 
      id_categoria: [''],
      cif: ['', [Validators.pattern(nifRegex)]], 
      direccio: [''],
      latitud: [null],  
      longitud: [null]
    });

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

    this.regForm.get('id_sector')?.valueChanges.subscribe(pareId => {
      const pareSeleccionat = this.categoriesPare.find(c => c.id_categoria == pareId);
      this.subcategoriesActives = pareSeleccionat ? pareSeleccionat.subcategories : [];
      this.regForm.get('id_categoria')?.setValue('');
    });
  }

  ngOnInit() {
    this.carregarCategories();
    this.configurarAutocompletarAdreca();
  }

  carregarCategories() {
    this.http.get<any[]>('http://localhost:8000/api/categories').subscribe({
      next: (data) => this.categoriesPare = data,
      error: (err) => console.error(err)
    });
  }

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
    const nomNet = adreca.display_name.split(', Lleida')[0] + ', Lleida';
    this.regForm.get('direccio')?.setValue(nomNet);
    
    // GUARDA LES CORDENADES AL FORMULARI D'AMAGATOTIS (Separades per a la BD!)
    this.regForm.get('latitud')?.setValue(parseFloat(adreca.lat));
    this.regForm.get('longitud')?.setValue(parseFloat(adreca.lon));
    
    this.adrecesSugerides = []; 
  }

  enviar() {
    if (this.regForm.invalid) {
      this.regForm.markAllAsTouched();
      return;
    }

    this.loading = true;
    this.errorMsg = '';

    const dadesEnviament = { ...this.regForm.value };
    delete dadesEnviament.id_sector;

    this.auth.register(dadesEnviament).subscribe({
      next: () => {
        this.loading = false;
        this.router.navigate(['/login'], { queryParams: { registered: 'true' }});
      },
      error: (err) => {
        this.loading = false;
        this.errorMsg = err.error?.message || 'Error en el registre.';
      }
    });
  }
}