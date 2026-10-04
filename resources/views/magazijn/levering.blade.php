<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Levering Informatie') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p class="mb-4 text-sm text-gray-600">
                        {{ $product->Naam }} &middot; {{ __('Barcode') }}: {{ $product->Barcode }}
                    </p>

                    {{-- Boven de tabel: leveranciersgegevens --}}
                    <dl class="mb-6 space-y-1.5 text-base">
                        <div class="flex flex-wrap gap-x-2">
                            <dt class="font-semibold text-gray-900">{{ __('Naam leverancier') }}:</dt>
                            <dd class="text-gray-700">{{ $leverancier?->Naam ?? __('Onbekend') }}</dd>
                        </div>
                        <div class="flex flex-wrap gap-x-2">
                            <dt class="font-semibold text-gray-900">{{ __('Contactpersoon leverancier') }}:</dt>
                            <dd class="text-gray-700">{{ $leverancier?->ContactPersoon ?? __('Onbekend') }}</dd>
                        </div>
                        <div class="flex flex-wrap gap-x-2">
                            <dt class="font-semibold text-gray-900">{{ __('Leveranciernummer') }}:</dt>
                            <dd class="text-gray-700">{{ $leverancier?->LeverancierNummer ?? __('Onbekend') }}</dd>
                        </div>
                        <div class="flex flex-wrap gap-x-2">
                            <dt class="font-semibold text-gray-900">{{ __('Mobiel') }}:</dt>
                            <dd class="text-gray-700">{{ $leverancier?->Mobiel ?? __('Onbekend') }}</dd>
                        </div>
                    </dl>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700">
                                        {{ __('Naam Product') }}
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700">
                                        {{ __('Datum laatste levering') }}
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700">
                                        {{ __('Aantal') }}
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700">
                                        {{ __('Eerstvolgende levering') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @if (! $heeftVoorraad)
                                    {{-- Scenario 2: exacte melding staat in de tabel --}}
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-gray-900">
                                            {{ $geenVoorraadMelding }}
                                        </td>
                                    </tr>
                                @elseif ($leveringen->isEmpty())
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                                            {{ __('Van dit product zijn nog geen leveringen bekend.') }}
                                        </td>
                                    </tr>
                                @else
                                    {{-- Scenario 1: leverdata, gesorteerd op Datum laatste levering oplopend --}}
                                    @foreach ($leveringen as $levering)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-900">
                                                {{ $product->Naam }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-900">
                                                {{ $levering->DatumLevering->format('d-m-Y') }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-700">
                                                {{ $levering->Aantal }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-700">
                                                {{ $levering->DatumEerstVolgendeLevering?->format('d-m-Y') ?? __('-') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        <a href="{{ route('magazijn.index') }}"
                           class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                            {{ __('Terug naar Overzicht Magazijn Jamin') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (! $heeftVoorraad)
        {{-- Scenario 2: na 4 seconden automatisch terug naar het overzicht --}}
        <script>
            setTimeout(function () {
                window.location = @json(route('magazijn.index'));
            }, 4000);
        </script>
    @endif
</x-app-layout>
