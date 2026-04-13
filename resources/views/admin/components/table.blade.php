<div class="overflow-x-auto w-full rounded-xl bg-white shadow-sm border border-slate-100">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-[#FDFBF7] text-slate-600 text-[11px] font-bold uppercase tracking-wider">
                @foreach($headers as $header)
                    <th class="py-4 px-6 {{ is_array($header) && isset($header['class']) ? $header['class'] : '' }}">
                        {{ is_array($header) ? $header['label'] : $header }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            {{ $slot }}
        </tbody>
    </table>
</div>
