@extends('admin.layouts.app')

@section('title', 'Comunicaciones Internas - SportReserve')

@php
    $inboxData = $inbox->map(function ($mensaje) {
        return [
            'id' => $mensaje->id,
            'sender' => $mensaje->remitente->name ?? 'Desconocido',
            'subject' => $mensaje->asunto,
            'date' => optional($mensaje->created_at)->format('d/m/Y H:i'),
            'read' => (bool) $mensaje->leido,
        ];
    });

    $sentData = $sent->map(function ($mensaje) {
        return [
            'id' => $mensaje->id,
            'receiver' => $mensaje->destinatario->name ?? 'Desconocido',
            'subject' => $mensaje->asunto,
            'date' => optional($mensaje->created_at)->format('d/m/Y H:i'),
            'read' => (bool) $mensaje->leido,
        ];
    });
@endphp

@section('content')
    <div x-data="communicationsPanel(@json($inboxData), @json($sentData), {
            inbox: '{{ route('admin.mensajes.index') }}',
            sent: '{{ route('admin.mensajes.enviados') }}',
            store: '{{ route('admin.mensajes.store') }}',
            markReadBase: '{{ url('/admin/mensajes') }}',
            destroyBase: '{{ url('/admin/mensajes') }}',
            notifications: '{{ route('admin.comunicaciones.notifications') }}',
            notificationsReadAll: '{{ route('admin.comunicaciones.notifications.readall') }}',
        })" class="space-y-10">

        {{-- Encabezado --}}
        <div class="rounded-3xl border border-emerald-50 bg-gradient-to-r from-emerald-50 to-white shadow-lg p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <p class="text-xs uppercase tracking-[0.35em] text-emerald-500 mb-2">Panel</p>
                <h1 class="text-3xl fw-bold text-emerald-900 mb-1">Comunicaciones Internas</h1>
                <p class="text-gray-600 mb-0">Gestiona mensajes entre administradores y revisa las notificaciones generadas por el sistema.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button @click="openComposer"
                    class="inline-flex items-center gap-2 rounded-full px-5 py-2 bg-emerald-600 text-white fw-semibold shadow hover:bg-emerald-700 transition">
                    <i class="bi bi-pencil-square"></i> Nuevo mensaje
                </button>
                <button @click="toggleDrawer"
                    class="relative inline-flex items-center gap-2 rounded-full px-5 py-2 border border-emerald-500 text-emerald-700 bg-white hover:bg-emerald-50 transition">
                    <i class="bi bi-bell"></i> Ver notificaciones
                    <span x-show="notificationCount > 0"
                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full text-xs px-2 py-0.5"
                        x-text="notificationCount"></span>
                </button>
            </div>
        </div>

        {{-- Notificaciones del sistema --}}
        <div class="rounded-3xl border border-gray-100 shadow-lg bg-white p-6 space-y-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center rounded-full bg-emerald-100 text-emerald-700 w-10 h-10">
                            <i class="bi bi-broadcast"></i>
                        </span>
                        <div>
                            <h2 class="fw-semibold text-lg mb-0">Notificaciones del sistema</h2>
                            <p class="text-sm text-gray-500 mb-0">Reservas, comprobantes y nuevos usuarios reportados en tiempo real.</p>
                        </div>
                    </div>
                </div>
                <button @click="markAllRead"
                    class="text-sm text-emerald-600 hover:text-emerald-700 underline underline-offset-4">Marcar todas como leídas</button>
            </div>

            <div class="grid gap-3">
                <template x-for="notification in notifications" :key="notification.id">
                    <div class="flex flex-col md:flex-row md:items-center gap-3 rounded-2xl border border-gray-100 p-4 transition hover:-translate-y-0.5 hover:shadow-md"
                        :class="notification.read ? 'bg-white' : 'bg-emerald-50/70'">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl"
                            :class="{
                                'bg-emerald-100 text-emerald-700': notification.type === 'reserva',
                                'bg-amber-100 text-amber-700': notification.type === 'comprobante',
                                'bg-sky-100 text-sky-700': notification.type === 'usuario'
                            }">
                            <template x-if="notification.type === 'reserva'">
                                <i class="bi bi-calendar-event"></i>
                            </template>
                            <template x-if="notification.type === 'comprobante'">
                                <i class="bi bi-receipt"></i>
                            </template>
                            <template x-if="notification.type === 'usuario'">
                                <i class="bi bi-person-plus"></i>
                            </template>
                        </div>
                        <div class="flex-1">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                                <div>
                                    <p class="fw-semibold mb-1" x-text="notification.title"></p>
                                    <p class="text-sm text-gray-600 mb-0" x-text="notification.description"></p>
                                </div>
                                <div class="text-sm text-gray-500" x-text="notification.relativeTime"></div>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs fw-semibold"
                            :class="notification.read ? 'bg-gray-100 text-gray-500' : 'bg-emerald-100 text-emerald-700'">
                            <span x-text="notification.read ? 'Leído' : 'Nuevo'"></span>
                        </span>
                    </div>
                </template>
            </div>
        </div>

        {{-- Mensajes internos --}}
        <div class="rounded-3xl border border-gray-100 shadow-lg bg-white p-6 space-y-6" x-data="{ tab: 'inbox' }">
            <div class="flex flex-wrap items-center gap-3">
                <button @click="tab = 'inbox'"
                    :class="tab === 'inbox' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600'"
                    class="px-4 py-2 rounded-full fw-semibold transition">
                    Bandeja de entrada
                </button>
                <button @click="tab = 'sent'"
                    :class="tab === 'sent' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600'"
                    class="px-4 py-2 rounded-full fw-semibold transition">
                    Enviados
                </button>
            </div>

            {{-- Bandeja de entrada --}}
            <div x-show="tab === 'inbox'" x-cloak class="rounded-2xl border border-gray-100 overflow-hidden">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-emerald-50">
                        <tr>
                            <th>Remitente</th>
                            <th>Asunto</th>
                            <th>Fecha</th>
                            <th class="text-end">Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="message in inbox" :key="message.id">
                            <tr :class="message.read ? '' : 'bg-emerald-50/60'">
                                <td x-text="message.sender"></td>
                                <td x-text="message.subject"></td>
                                <td x-text="message.date"></td>
                                <td class="text-end">
                                    <span class="px-3 py-1 rounded-full text-xs fw-semibold"
                                        :class="message.read ? 'bg-gray-100 text-gray-600' : 'bg-emerald-100 text-emerald-700'">
                                        <span x-text="message.read ? 'Leído' : 'Nuevo'"></span>
                                    </span>
                                </td>
                                <td class="text-end space-x-1">
                                    <button class="btn btn-outline-emerald btn-sm" x-show="!message.read"
                                        @click="markMessageAsRead(message.id)">
                                        Marcar leído
                                    </button>
                                    <button class="btn btn-danger-soft btn-sm"
                                        @click="deleteMessage(message.id)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="inbox.length === 0">
                            <td colspan="5" class="text-center text-gray-500 py-4">No tienes mensajes en tu bandeja.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Enviados --}}
            <div x-show="tab === 'sent'" x-cloak class="rounded-2xl border border-gray-100 overflow-hidden">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-emerald-50">
                        <tr>
                            <th>Destinatario</th>
                            <th>Asunto</th>
                            <th>Fecha</th>
                            <th class="text-end">Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="message in sent" :key="message.id">
                            <tr>
                                <td x-text="message.receiver"></td>
                                <td x-text="message.subject"></td>
                                <td x-text="message.date"></td>
                                <td class="text-end">
                                    <span class="px-3 py-1 rounded-full text-xs fw-semibold"
                                        :class="message.read ? 'bg-gray-100 text-gray-600' : 'bg-emerald-100 text-emerald-700'">
                                        <span x-text="message.read ? 'Leído' : 'Nuevo'"></span>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-danger-soft btn-sm"
                                        @click="deleteMessage(message.id)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="sent.length === 0">
                            <td colspan="5" class="text-center text-gray-500 py-4">Aún no has enviado mensajes.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal nuevo mensaje --}}
        <div x-show="modalOpen" x-cloak
            class="fixed inset-0 z-40 bg-black/25 backdrop-blur-sm flex items-center justify-center p-4"
            @keydown.escape.window="modalOpen = false">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-xl p-6 relative">
                <button @click="modalOpen = false"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition">
                    <i class="bi bi-x-lg"></i>
                </button>
                <h3 class="text-xl fw-bold text-emerald-900 mb-4">Nuevo mensaje interno</h3>
                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <label class="form-label fw-semibold">Destinatario (ID de usuario)</label>
                        <input type="number" class="form-control rounded-2xl" placeholder="Ej. 5" x-model="form.to" required>
                    </div>
                    <div>
                        <label class="form-label fw-semibold">Asunto</label>
                        <input type="text" class="form-control rounded-2xl" placeholder="Ej. Seguimiento reserva #2051"
                            x-model="form.subject" required>
                    </div>
                    <div>
                        <label class="form-label fw-semibold">Contenido</label>
                        <textarea class="form-control rounded-2xl" rows="5" placeholder="Escribe tu mensaje..." x-model="form.message"
                            required></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" class="btn btn-soft-gray" @click="modalOpen = false">Cancelar</button>
                        <button type="submit" class="btn btn-emerald">Enviar mensaje</button>
                    </div>
                </form>
                <div x-show="formSuccess"
                    class="alert alert-success mt-4 rounded-2xl d-flex align-items-center gap-2 fw-semibold" x-cloak>
                    Mensaje enviado correctamente
                </div>
            </div>
        </div>

        {{-- Drawer de notificaciones --}}
        <div x-show="drawerOpen" x-cloak
            class="fixed inset-0 z-30 flex justify-end bg-black/20" @click.self="drawerOpen = false">
            <div class="w-full md:w-96 bg-white h-full shadow-2xl p-6 space-y-4 overflow-y-auto border-l border-gray-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg fw-semibold">Notificaciones</h3>
                    <button class="text-gray-500" @click="drawerOpen = false"><i class="bi bi-x-lg"></i></button>
                </div>
                <template x-if="notifications.length === 0">
                    <p class="text-sm text-gray-500">Sin notificaciones pendientes.</p>
                </template>
                <template x-for="notification in notifications" :key="notification.id">
                    <div class="border rounded-2xl p-4 space-y-2">
                        <div class="flex justify-between text-sm fw-semibold">
                            <span x-text="notification.title"></span>
                            <span class="text-gray-500" x-text="notification.relativeTime"></span>
                        </div>
                        <p class="text-sm text-gray-600" x-text="notification.description"></p>
                        <span class="text-xs px-2 py-0.5 rounded-full"
                            :class="notification.read ? 'bg-gray-100 text-gray-600' : 'bg-emerald-100 text-emerald-700'">
                            <span x-text="notification.read ? 'Leído' : 'Nuevo'"></span>
                        </span>
                    </div>
                </template>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="//unpkg.com/alpinejs" defer></script>
        <script>
            function communicationsPanel(initialInbox = [], initialSent = [], routes = {}) {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                return {
                    modalOpen: false,
                    drawerOpen: false,
                    formSuccess: false,
                    form: { to: '', subject: '', message: '' },
                    routes,
                    csrf: token,
                    notificationCount: 0,
                    notifications: [],
                    inbox: initialInbox,
                    sent: initialSent,

                    init() {
                        this.fetchNotifications();
                        this.refreshInbox();
                        this.refreshSent();
                        setInterval(() => this.fetchNotifications(), 30000);
                    },

                    openComposer() {
                        this.modalOpen = true;
                        this.formSuccess = false;
                    },

                    toggleDrawer() {
                        this.drawerOpen = !this.drawerOpen;
                    },

                    submitForm() {
                        fetch(this.routes.store, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrf,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                destinatario_id: this.form.to,
                                asunto: this.form.subject,
                                contenido: this.form.message,
                            }),
                        }).then(res => res.json())
                            .then(() => {
                                this.formSuccess = true;
                                this.form = { to: '', subject: '', message: '' };
                                this.refreshInbox();
                                this.refreshSent();
                                setTimeout(() => {
                                    this.formSuccess = false;
                                    this.modalOpen = false;
                                }, 1500);
                            });
                    },

                    markAllRead() {
                        fetch(this.routes.notificationsReadAll, {
                            method: 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': this.csrf,
                                'Accept': 'application/json',
                            },
                        }).then(() => this.fetchNotifications());
                    },

                    fetchNotifications() {
                        fetch(this.routes.notifications)
                            .then(res => res.json())
                            .then(data => {
                                this.notificationCount = data.count;
                                this.notifications = data.items.map(item => ({
                                    id: item.id,
                                    type: item.type,
                                    title: item.title,
                                    description: item.body,
                                    relativeTime: this.relativeTime(item.created_at),
                                    read: item.status === 'read',
                                }));
                            });
                    },

                    refreshInbox() {
                        fetch(this.routes.inbox)
                            .then(res => res.json())
                            .then(({ items }) => {
                                this.inbox = items.map(item => ({
                                    id: item.id,
                                    sender: item.remitente?.name ?? 'Desconocido',
                                    subject: item.asunto,
                                    date: this.formatDate(item.created_at),
                                    read: item.leido,
                                }));
                            });
                    },

                    refreshSent() {
                        fetch(this.routes.sent)
                            .then(res => res.json())
                            .then(({ items }) => {
                                this.sent = items.map(item => ({
                                    id: item.id,
                                    receiver: item.destinatario?.name ?? 'Desconocido',
                                    subject: item.asunto,
                                    date: this.formatDate(item.created_at),
                                    read: item.leido,
                                }));
                            });
                    },

                    markMessageAsRead(id) {
                        fetch(`${this.routes.markReadBase}/${id}/leido`, {
                            method: 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': this.csrf,
                                'Accept': 'application/json',
                            },
                        }).then(() => this.refreshInbox());
                    },

                    deleteMessage(id) {
                        fetch(`${this.routes.destroyBase}/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': this.csrf,
                                'Accept': 'application/json',
                            },
                        }).then(() => {
                            this.refreshInbox();
                            this.refreshSent();
                        });
                    },

                    formatDate(dateString) {
                        return dateString ? new Date(dateString).toLocaleString('es-CO') : '';
                    },

                    relativeTime(dateString) {
                        if (!dateString) return '';
                        const diff = Date.now() - new Date(dateString).getTime();
                        const minutes = Math.round(diff / 60000);
                        if (minutes < 60) return `Hace ${minutes} min`;
                        const hours = Math.round(minutes / 60);
                        if (hours < 24) return `Hace ${hours} h`;
                        const days = Math.round(hours / 24);
                        return `Hace ${days} d`;
                    },
                };
            }
        </script>
    @endpush
@endsection
