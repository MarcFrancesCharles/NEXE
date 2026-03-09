import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { Auth } from '../../core/services/auth';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterLink],
  templateUrl: './register.html',
  styleUrl: './register.css'
})
export class Register {
  regForm: FormGroup;
  loading = false;
  errorMsg = '';

  private fb = inject(FormBuilder);
  private auth = inject(Auth);
  private router = inject(Router);

  constructor() {
    this.regForm = this.fb.group({
      nom: ['', [Validators.required, Validators.minLength(2)]],
      correu: ['', [Validators.required, Validators.email]],
      contrasenya: ['', [Validators.required, Validators.minLength(8)]],
      rol: ['ESTANDARD'] // Per defecte tothom és client
    });
  }

  enviar() {
    if (this.regForm.invalid) return;

    this.loading = true;
    this.errorMsg = '';

    this.auth.register(this.regForm.value).subscribe({
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