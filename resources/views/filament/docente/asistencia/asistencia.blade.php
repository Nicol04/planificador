<div style="margin-bottom:1rem;"><strong>Semanas: </strong><span id="weeks-count">{{ $weeksCount }}</span></div>

<div class="calendar-wrapper" style="overflow:auto;">
	<table class="filament-tables-table" style="border-collapse:collapse;">
		<thead>
			<tr>
				<th>Semana</th><th>L</th><th>Ma</th><th>Mi</th><th>J</th><th>V</th>
			</tr>
		</thead>
		<tbody>
			@foreach($matrix as $i => $week)
				<tr>
					<td style="vertical-align:middle; padding:0.5rem;">{{ $i + 1 }}</td>
					@foreach(['L','Ma','Mi','J','V'] as $dayKey)
						@php $cell = $week[$dayKey]; @endphp
						@if($cell['date'])
							<td style="padding:0.25rem 0.5rem; text-align:center;">
								<div style="display:flex; flex-direction:column; align-items:center;">
									{{-- Mostrar solo el día del mes --}}
									<div style="font-size:0.9rem;">{{ \Carbon\Carbon::parse($cell['date'])->format('d') }}</div>
									<label style="margin-top:0.25rem;">
										<input type="checkbox" id="no_class_{{ $cell['date'] }}" class="no-class-checkbox" data-date="{{ $cell['date'] }}"> No clase
									</label>
									<input type="hidden" name="dias[{{ $cell['date'] }}]" id="input_{{ $cell['date'] }}" value="0">
								</div>
							</td>
						@else
							<td style="padding:0.25rem 0.5rem; text-align:center;color:#aaa;">—</td>
						@endif
					@endforeach
				</tr>
			@endforeach
		</tbody>
	</table>
</div>

<div style="margin-top:1rem;"><strong>Estudiantes del aula:</strong>
	@if($students->count())
		<table class="filament-tables-table" style="margin-top:0.5rem;">
			<thead><tr><th>Nombre completo</th></tr></thead>
			<tbody>
				@foreach($students as $s)
					<tr><td>{{ $s->nombres }} {{ $s->apellidos }}</td></tr>
				@endforeach
			</tbody>
		</table>
	@else
		<div style="margin-top:0.5rem;">No se encontraron estudiantes (asegure que el docente tiene aula_id).</div>
	@endif
</div>

<style>
	.no-class-cell{background:#e6f0ff;}
	.filament-tables-table th, .filament-tables-table td{border:1px solid #e6e6e6; padding:6px;}
	.filament-tables-table{width:100%;}
</style>

<script>
	(function(){
		document.querySelectorAll('.no-class-checkbox').forEach(function(cb){
			cb.addEventListener('change', function(){
				var date = this.dataset.date;
				var input = document.getElementById('input_' + date);
				if(input){
					input.value = this.checked ? '1' : '0';
				}
				if(this.checked){
					this.closest('td').classList.add('no-class-cell');
				}else{
					this.closest('td').classList.remove('no-class-cell');
				}
			});
		});
	})();
</script>
