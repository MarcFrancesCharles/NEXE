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
        //Assegurem que agafem el rol bé
        const rolUsuari = res.rol || res.usuari?.rol;

        this.auth.guardarToken(res.token, rolUsuari);

        //SEMAFOR
        if (rolUsuari === 'COMERÇ' || rolUsuari === 'ADMIN') {
          this.router.navigate(['/la-meva-botiga']);
        } else {
          this.router.navigate(['/perfil']); //CLIENTS NORMALS VAN A PERFIL, COMERÇS VAN A LA SEVA BOTIGA
        }
      },
      error: () => {
        this.loading = false;
        this.errorMsg = 'Credencials incorrectes. Torna-ho a provar.';
      }
    });
  }
}