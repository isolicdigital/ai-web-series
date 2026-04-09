<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Assign Subscription') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.subscriptions.store') }}">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Owner</label>
                            <select name="user_id" class="w-full border-gray-300 rounded-md shadow-sm">
                                @foreach($owners as $owner)
                                    <option value="{{ $owner->id }}">{{ $owner->name }} ({{ $owner->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Plan</label>
                            <select name="plan_id" class="w-full border-gray-300 rounded-md shadow-sm">
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Assign</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>