import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../../environments/environment';

@Component({
  selector: 'app-careers',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './careers.html',
  styleUrl: './careers.css'
})
export class Careers {
  careersForm: FormGroup;
  loading = false;
  successMsg = '';
  errorMsg = '';
  selectedFileName = '';

  private fb = inject(FormBuilder);
  private router = inject(Router);
  private http = inject(HttpClient);

  constructor() {
    this.careersForm = this.fb.group({
      nom: ['', [Validators.required, Validators.minLength(2)]],
      correu: ['', [Validators.required, Validators.email]],
      posicio: ['ADMIN', [Validators.required]],
      missatge: ['', [Validators.required, Validators.minLength(10)]],
      cv: [null, [Validators.required]]
    });
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

    this.http.post<any>(`${environment.apiUrl}/careers`, formData).subscribe({
      next: (res) => {
        this.loading = false;
        this.successMsg = 'Gràcies per el teu interès! Hem rebut la teva sol·licitud correctament i ens posarem en contacte amb tu aviat.';
        this.careersForm.reset({
          nom: '',
          correu: '',
          posicio: 'ADMIN',
          missatge: '',
          cv: null
        });
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
