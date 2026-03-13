import { Component, inject, OnInit } from '@angular/core';
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
export class Register implements OnInit {
  regForm: FormGroup;
  loading = false;
  errorMsg = '';
  categories: any[] = [];

  private fb = inject(FormBuilder);
  private auth = inject(Auth);
  private router = inject(Router);

  constructor() {
    this.regForm = this.fb.group({
      nom: ['', [Validators.required, Validators.minLength(2)]],
      correu: ['', [Validators.required, Validators.email]],
      contrasenya: ['', [Validators.required, Validators.minLength(8)]],
      rol: ['ESTANDARD', [Validators.required]],
      // Camps extra pel comerç
      id_categoria: [''],
      cif: ['']
    });

    // Validacions condicionals segons el rol
    this.regForm.get('rol')?.valueChanges.subscribe(rol => {
      const catCtrl = this.regForm.get('id_categoria');
      const cifCtrl = this.regForm.get('cif');

      if (rol === 'COMERC') {
        catCtrl?.setValidators([Validators.required]);
        cifCtrl?.setValidators([Validators.required]);
      } else {
        catCtrl?.clearValidators();
        cifCtrl?.clearValidators();
      }
      catCtrl?.updateValueAndValidity();
      cifCtrl?.updateValueAndValidity();
    });
  }

  ngOnInit() {
    this.carregarCategories();
  }

  carregarCategories() {
    this.auth.getCategories().subscribe({
      next: (res) => this.categories = res,
      error: (err) => console.error('Error carregant categories', err)
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