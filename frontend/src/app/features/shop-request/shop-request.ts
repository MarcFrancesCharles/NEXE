import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Auth } from '../../core/services/auth';
import { Subject, of } from 'rxjs';
import { debounceTime, distinctUntilChanged, switchMap, catchError } from 'rxjs/operators';
import { environment } from '../../../../environments/environment';

@Component({
  selector: 'app-shop-request',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './shop-request.html',
  styleUrl: './shop-request.css'
})
export class ShopRequest implements OnInit {
  requestForm: FormGroup;
  loading = false;
  errorMsg = '';
  successMsg = '';
  showSuccessModal = false;
  
  categoriesPare: any[] = [];
  subcategoriesActives: any[] = [];

  adrecaSubject = new Subject<string>();
  adrecesSugerides: any[] = [];
  buscantAdreca = false;

  imatgeFile: File | null = null;
  imatgePreview: string | null = null;

  private fb = inject(FormBuilder);
  private auth = inject(Auth);
  private router = inject(Router);
  private http = inject(HttpClient);

  constructor() {
    this.requestForm = this.fb.group({
      nom_comercial: ['', [Validators.required, Validators.minLength(2)]],
      descripcio: ['', [Validators.required, Validators.maxLength(255)]],
      id_sector: ['', [Validators.required]],
      id_categoria: ['', [Validators.required]],
      cif: ['', [Validators.required, Validators.pattern(/^[0-9A-Z][0-9]{7}[0-9A-Z]$/i)]],
      telefon: ['', [Validators.required, Validators.pattern(/^[0-9]{9}$/)]],
      email_contacte: ['', [Validators.required, Validators.email]],
      enllac_web: ['', [Validators.pattern(/^https?:\/\/.*/)]],
      instagram: [''],
      direccio: ['', [Validators.required]],
      imatge_url: [''],
      latitud: [null, [Validators.required]],
      longitud: [null, [Validators.required]]
    });

    this.requestForm.get('id_sector')?.valueChanges.subscribe(pareId => {
      if (pareId) {
        const pareSeleccionat = this.categoriesPare.find(c => String(c.id_categoria) === String(pareId));
        this.subcategoriesActives = (pareSeleccionat && pareSeleccionat.subcategories) ? pareSeleccionat.subcategories : [];
      } else {
        this.subcategoriesActives = [];
      }
      this.requestForm.get('id_categoria')?.setValue('');
    });

    // Si l'usuari escriu una URL, netegem l'arxiu pujat
    this.requestForm.get('imatge_url')?.valueChanges.subscribe(val => {
      if (val && this.imatgeFile) {
        this.imatgeFile = null;
        this.imatgePreview = null;
      }
    });
  }

  ngOnInit() {
    this.carregarCategories();
    this.configurarAutocompletarAdreca();
  }

  carregarCategories() {
    this.http.get<any[]>(`${environment.apiUrl}/categories`).subscribe({
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
    this.requestForm.get('direccio')?.setValue(nomNet);
    this.requestForm.get('latitud')?.setValue(parseFloat(adreca.lat));
    this.requestForm.get('longitud')?.setValue(parseFloat(adreca.lon));
    this.adrecesSugerides = []; 
  }

  onFileSelected(event: any) {
    const file = event.target.files[0];
    if (file) {
      this.imatgeFile = file;
      this.requestForm.get('imatge_url')?.setValue(''); // Netegem la URL si pugem arxiu
      
      const reader = new FileReader();
      reader.onload = () => {
        this.imatgePreview = reader.result as string;
      };
      reader.readAsDataURL(file);
    }
  }

  // Helpers per a la Preview
  getSectorInfo() {
    const id = this.requestForm.get('id_sector')?.value;
    return this.categoriesPare.find(c => String(c.id_categoria) === String(id));
  }

  getEspecialitatNom() {
    const id = this.requestForm.get('id_categoria')?.value;
    const sub = this.subcategoriesActives.find(s => String(s.id_categoria) === String(id));
    return sub ? sub.nom_cat : null;
  }

  getPreviewImage() {
    if (this.imatgePreview) return this.imatgePreview;
    
    const url = this.requestForm.get('imatge_url')?.value;
    return url || 'https://images.unsplash.com/photo-1534723452862-4c874018d66d?auto=format&fit=crop&q=80&w=800';
  }

  enviar() {
    if (this.requestForm.invalid) {
      this.requestForm.markAllAsTouched();
      return;
    }

    this.loading = true;
    this.errorMsg = '';
    
    const token = this.auth.obtenirToken();
    const headers = new HttpHeaders({ 'Authorization': `Bearer ${token}` });
    
    const formData = new FormData();
    Object.keys(this.requestForm.value).forEach(key => {
      const val = this.requestForm.value[key];
      if (val !== null && val !== undefined) {
        formData.append(key, val);
      }
    });

    if (this.imatgeFile) {
      formData.append('imatge_file', this.imatgeFile);
    }

    // Eliminem camps que el backend no espera o que enviem per separat
    formData.delete('id_sector');
    formData.delete('direccio');

    this.http.post(`${environment.apiUrl}/solicituds-comerc`, formData, { headers }).subscribe({
      next: () => {
        this.loading = false;
        this.showSuccessModal = true;
        this.successMsg = '';
      },
      error: (err) => {
        this.loading = false;
        this.errorMsg = err.error?.missatge || err.error?.error || 'Error en enviar la sol·licitud.';
      }
    });
  }

  closeSuccessModal() {
    this.showSuccessModal = false;
    this.router.navigate(['/']);
  }
}
