import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { Auth } from '../../core/services/auth';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterLink],
  templateUrl: './login.html',
  styleUrl: './login.css'
})
export class Login {
  loginForm: FormGroup;
  loading = false;
  errorMsg = '';

  private fb = inject(FormBuilder);
  private auth = inject(Auth);
  private router = inject(Router);

  constructor() {
    this.loginForm = this.fb.group({
      correu: ['', [Validators.required, Validators.email]],
      contrasenya: ['', [Validators.required]]
    });
  }

  entrar() {
    if (this.loginForm.invalid) return;
    this.loading = true;
    this.errorMsg = '';

    this.auth.login(this.loginForm.value).subscribe({
      next: (res) => {
        this.auth.guardarToken(res.token, res.rol);
        this.router.navigate(['/perfil']);
      },
      error: () => {
        this.loading = false;
        this.errorMsg = 'Credencials incorrectes. Torna-ho a provar.';
      }
    });
  }
}