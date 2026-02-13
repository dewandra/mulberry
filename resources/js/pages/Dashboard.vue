<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    stats: Object,
});
</script>

<template>
    <Head title="Dashboard" />
    
    <div class="min-h-screen bg-gray-100">
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-6">
                📊 Project Status Dashboard
            </h1>
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-gray-500 text-sm">Active Projects</div>
                    <div class="text-3xl font-bold text-blue-600">{{ stats.active }}</div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-gray-500 text-sm">Awaiting Feedback</div>
                    <div class="text-3xl font-bold text-yellow-600">{{ stats.awaiting_feedback }}</div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-gray-500 text-sm">High Priority</div>
                    <div class="text-3xl font-bold text-red-600">{{ stats.high_priority }}</div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-gray-500 text-sm">This Week</div>
                    <div class="text-3xl font-bold text-green-600">{{ stats.this_week }}</div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Welcome to Project Status Dashboard</h2>
                <p class="text-gray-600">
                    Manage your creative projects with ease. Track progress, send previews, and collect feedback.
                </p>
            </div>
        </div>
    </div>
</template>