import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '../../../../environments/environment';
import { Auth } from '../../core/services/auth';

@Component({
  selector: 'app-careers',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './careers.html',
  styleUrl: './careers.css'
})
export class Careers implements OnInit {
  careersForm: FormGroup;
  loading = false;
  successMsg = '';
  errorMsg = '';
  selectedFileName = '';

  private fb = inject(FormBuilder);
  private router = inject(Router);
  private http = inject(HttpClient);
  private auth = inject(Auth);

  constructor() {
    this.careersForm = this.fb.group({
      nom: [{ value: '', disabled: true }, [Validators.required, Validators.minLength(2)]],
      correu: [{ value: '', disabled: true }, [Validators.required, Validators.email]],
      posicio: ['', [Validators.required]],
      missatge: ['', [Validators.required, Validators.minLength(10)]],
      cv: [null]
    });
  }

  estaLogat(): boolean {
    return !!this.auth.obtenirToken();
  }

  ngOnInit() {
    if (this.estaLogat()) {
      const headers = new HttpHeaders({ 'Authorization': `Bearer ${this.auth.obtenirToken()}` });
      this.http.get<any>(`${environment.apiUrl}/perfil-meu`, { headers }).subscribe({
        next: (res) => {
          this.careersForm.patchValue({
            nom: res.nom || '',
            correu: res.correu || ''
          });
        }
      });
    }
  }

  anarALogin() {
    this.router.navigate(['/login']);
  }

  anarARegister() {
    this.router.navigate(['/register']);
  }

  onFileChange(event: any) {
    const file = event.target.files[0];
    if (file) {
      this.selectedFileName = file.name;
      this.careersForm.patchValue({
        cv: file
      });
      this.careersForm.get('cv')?.updateValueAndValidity();
    }
  }

  enviar() {
    if (!this.estaLogat()) {
      this.errorMsg = 'Has d\'iniciar sessió per enviar sol·licituds.';
      return;
    }

    if (this.careersForm.invalid) {
      this.careersForm.markAllAsTouched();
      return;
    }

    this.loading = true;
    this.errorMsg = '';
    this.successMsg = '';

    const formData = new FormData();
    formData.append('nom', this.careersForm.get('nom')?.value);
    formData.append('correu', this.careersForm.get('correu')?.value);
    formData.append('posicio', this.careersForm.get('posicio')?.value);
    formData.append('missatge', this.careersForm.get('missatge')?.value);
    formData.append('cv', this.careersForm.get('cv')?.value);

    const headers = new HttpHeaders({ 'Authorization': `Bearer ${this.auth.obtenirToken()}` });

    this.http.post<any>(`${environment.apiUrl}/careers`, formData, { headers }).subscribe({
      next: (res) => {
        this.loading = false;
        this.successMsg = 'Sol·licitud tramesa correctament. Estigues pendent de les notificacions a la plataforma, ja que ens posarem en contacte amb tu per allà.';
        
        // Recarregar dades inicials després de reset
        this.ngOnInit();
        this.selectedFileName = '';
      },
      error: (err: any) => {
        this.loading = false;
        this.errorMsg = err.error?.message || 'Error en enviar la sol·licitud.';
      }
    });
  }

  tornar() {
    this.router.navigate(['/']);
  }
}
