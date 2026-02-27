<template>
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
                <!-- Backdrop -->
                <div
                    class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity"
                    @click="$emit('close')"
                />

                <!-- Modal Panel -->
                <div class="flex min-h-full items-center justify-center p-4">
                    <div
                        class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col"
                        @click.stop
                    >
                        <!-- Header -->
                        <div
                            class="flex items-center justify-between px-6 py-4 border-b border-gray-200 shrink-0"
                        >
                            <div>
                                <h2
                                    class="text-lg font-bold text-gray-900"
                                    style="
                                        font-family:
                                            &quot;Sora&quot;, sans-serif;
                                    "
                                >
                                    {{
                                        project
                                            ? "Edit Project"
                                            : "Create New Project"
                                    }}
                                </h2>
                                <p
                                    v-if="project"
                                    class="text-xs text-gray-500 mt-0.5 font-mono"
                                >
                                    {{ project.project_code }}
                                </p>
                            </div>
                            <button
                                @click="$emit('close')"
                                class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors"
                            >
                                <svg
                                    class="w-5 h-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="overflow-y-auto px-6 py-5 space-y-5 flex-1">
                            <!-- Project Name -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                >
                                    Project Name
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.project_name"
                                    type="text"
                                    placeholder="e.g. Interactive Campaign - Stage 13"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                    :class="{
                                        'border-red-400': errors.project_name,
                                    }"
                                />
                                <p
                                    v-if="errors.project_name"
                                    class="mt-1 text-xs text-red-600"
                                >
                                    {{ errors.project_name }}
                                </p>
                            </div>

                            <!-- Client + Priority (2-col) -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Client
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.client_id"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                        :class="{
                                            'border-red-400': errors.client_id,
                                        }"
                                    >
                                        <option value="">Select Companies</option>
                                        <option
                                            v-for="c in clients"
                                            :key="c.id"
                                            :value="c.id"
                                        >
                                            {{ c.company_name }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="errors.client_id"
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{ errors.client_id }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >Priority</label
                                    >
                                    <select
                                        v-model="form.priority"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                    >
                                        <option value="high">🔴 High</option>
                                        <option value="normal">
                                            🔵 Normal
                                        </option>
                                        <option value="low">⚪ Low</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Status -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Status</label
                                >
                                <select
                                    v-model="form.status"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                >
                                    <option value="brief">Brief</option>
                                    <option value="scheduled">Scheduled</option>
                                    <option value="work_in_progress">
                                        Work In Progress
                                    </option>
                                    <option value="preview_sent">
                                        Preview Sent
                                    </option>
                                    <option value="feedback_received">
                                        Feedback Received
                                    </option>
                                    <option value="artwork_approved">
                                        Artwork Approved
                                    </option>
                                    <option value="final_artwork_preparation">
                                        Final Artwork Preparation
                                    </option>
                                    <option value="fa_sent">FA Sent</option>
                                    <option value="project_closed">
                                        Project Closed
                                    </option>
                                </select>
                            </div>

                            <!-- Deadline + Report Date (2-col) -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >Deadline</label
                                    >
                                    <input
                                        v-model="form.deadline"
                                        type="date"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >Report Date</label
                                    >
                                    <input
                                        v-model="form.report_date"
                                        type="date"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                    />
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Description</label
                                >
                                <textarea
                                    v-model="form.description"
                                    rows="3"
                                    placeholder="Project description and requirements..."
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                />
                            </div>

                            <!-- Thumbnail Image -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Project Thumbnail</label
                                >
                                <!-- Preview -->
                                <div
                                    class="relative w-full h-36 rounded-lg border-2 border-dashed border-gray-300 overflow-hidden mb-2 flex items-center justify-center bg-gray-50 cursor-pointer hover:border-blue-400 transition-colors"
                                    @click="$refs.thumbnailInput.click()"
                                >
                                    <img
                                        v-if="thumbnailPreview"
                                        :src="thumbnailPreview"
                                        class="w-full h-full object-cover"
                                        alt="Thumbnail preview"
                                    />
                                    <div v-else class="text-center">
                                        <svg
                                            class="w-8 h-8 text-gray-300 mx-auto mb-1"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.5"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                            />
                                        </svg>
                                        <p class="text-xs text-gray-400">
                                            Click to upload image
                                        </p>
                                        <p class="text-xs text-gray-300">
                                            JPG, PNG, WEBP — max 2MB
                                        </p>
                                    </div>
                                    <!-- Change overlay -->
                                    <div
                                        v-if="thumbnailPreview"
                                        class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-30 transition-all flex items-center justify-center"
                                    >
                                        <span
                                            class="opacity-0 hover:opacity-100 text-white text-xs font-medium"
                                            >Change image</span
                                        >
                                    </div>
                                </div>
                                <input
                                    ref="thumbnailInput"
                                    type="file"
                                    accept="image/jpeg,image/png,image/jpg,image/webp"
                                    class="hidden"
                                    @change="handleThumbnail"
                                />
                                <button
                                    v-if="thumbnailPreview"
                                    type="button"
                                    @click="removeThumbnail"
                                    class="text-xs text-red-500 hover:text-red-700"
                                >
                                    Remove image
                                </button>
                                <p
                                    v-if="errors.thumbnail"
                                    class="mt-1 text-xs text-red-600"
                                >
                                    {{ errors.thumbnail }}
                                </p>
                            </div>

                            <!-- PIC Assignment -->
                            <div v-if="picUsers && picUsers.length > 0">
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Assign PICs</label
                                >
                                <div
                                    class="border border-gray-200 rounded-lg divide-y divide-gray-100 max-h-40 overflow-y-auto"
                                >
                                    <label
                                        v-for="pic in filteredPicUsers"
                                        :key="pic.id"
                                        class="flex items-center gap-3 px-3 py-2 hover:bg-gray-50 cursor-pointer transition-colors"
                                    >
                                        <input
                                            type="checkbox"
                                            :value="pic.id"
                                            v-model="form.pic_ids"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                        <div class="flex-1 min-w-0">
                                            <p
                                                class="text-sm font-medium text-gray-800 truncate"
                                            >
                                                {{ pic.full_name }}
                                            </p>
                                            <p
                                                class="text-xs text-gray-500 truncate"
                                            >
                                                {{ pic.email }}
                                            </p>
                                        </div>
                                    </label>
                                    <div
                                        v-if="filteredPicUsers.length === 0"
                                        class="px-3 py-4 text-sm text-center text-gray-400"
                                    >
                                        {{
                                            form.client_id
                                                ? "No PICs registered for this client yet"
                                                : "Select a client first"
                                        }}
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">
                                    PICs will only see projects they're assigned
                                    to.
                                </p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div
                            class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-2xl shrink-0"
                        >
                            <button
                                type="button"
                                @click="$emit('close')"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                @click="submit"
                                :disabled="processing"
                                class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                            >
                                <span
                                    v-if="processing"
                                    class="flex items-center gap-2"
                                >
                                    <svg
                                        class="animate-spin w-4 h-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle
                                            class="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            stroke-width="4"
                                        />
                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8v8H4z"
                                        />
                                    </svg>
                                    Saving...
                                </span>
                                <span v-else>{{
                                    project ? "Save Changes" : "Create Project"
                                }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, reactive, computed, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";

const props = defineProps({
    show: { type: Boolean, default: false },
    project: { type: Object, default: null },
    clients: { type: Array, default: () => [] },
    picUsers: { type: Array, default: () => [] },
});

const emit = defineEmits(["close", "saved"]);

const processing = ref(false);
const errors = ref({});
const thumbnailFile = ref(null);
const thumbnailPreview = ref(null);
const thumbnailInput = ref(null);

const form = reactive({
    project_name: "",
    client_id: "",
    status: "brief",
    priority: "normal",
    description: "",
    deadline: "",
    report_date: "",
    pic_ids: [],
});

const handleThumbnail = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    thumbnailFile.value = file;
    thumbnailPreview.value = URL.createObjectURL(file);
};

const removeThumbnail = () => {
    thumbnailFile.value = null;
    thumbnailPreview.value = null;
    if (thumbnailInput.value) thumbnailInput.value.value = "";
};

// Filter PICs by selected client — only show PICs belonging to that company
const filteredPicUsers = computed(() => {
    if (!form.client_id) return [];
    return props.picUsers.filter((p) => p.client_id === form.client_id);
});

// Clear PIC assignments when client changes
watch(
    () => form.client_id,
    (newId, oldId) => {
        if (oldId && newId !== oldId) form.pic_ids = [];
    },
);

// Populate form when editing
watch(
    () => props.project,
    (val) => {
        errors.value = {};
        thumbnailFile.value = null;
        thumbnailPreview.value = null;
        if (val) {
            form.project_name = val.project_name || "";
            form.client_id = val.client_id || "";
            form.status = val.status || "brief";
            form.priority = val.priority || "normal";
            form.description = val.description || "";
            form.deadline = val.deadline ? val.deadline.substring(0, 10) : "";
            form.report_date = val.report_date
                ? val.report_date.substring(0, 10)
                : "";
            form.pic_ids = val.pic_users ? val.pic_users.map((p) => p.id) : [];
            // Show existing thumbnail
            thumbnailPreview.value = val.thumbnail || null;
        } else {
            form.project_name = "";
            form.client_id = "";
            form.status = "brief";
            form.priority = "normal";
            form.description = "";
            form.deadline = "";
            form.report_date = "";
            form.pic_ids = [];
        }
    },
    { immediate: true },
);

const submit = () => {
    processing.value = true;
    errors.value = {};

    // Build FormData so file uploads work
    const data = new FormData();
    data.append("project_name", form.project_name);
    data.append("client_id", form.client_id);
    data.append("status", form.status);
    data.append("priority", form.priority);
    data.append("description", form.description || "");
    data.append("deadline", form.deadline || "");
    data.append("report_date", form.report_date || "");
    form.pic_ids.forEach((id) => data.append("pic_ids[]", id));
    if (thumbnailFile.value) {
        data.append("thumbnail", thumbnailFile.value);
    }

    const options = {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            processing.value = false;
            // Reset form fields
            form.project_name = "";
            form.client_id = "";
            form.status = "brief";
            form.priority = "normal";
            form.description = "";
            form.deadline = "";
            form.report_date = "";
            form.pic_ids = [];
            // Clear thumbnail
            thumbnailFile.value = null;
            thumbnailPreview.value = null;
            if (thumbnailInput.value) thumbnailInput.value.value = "";
            emit("saved");
        },
        onError: (errs) => {
            processing.value = false;
            errors.value = errs;
        },
    };

    if (props.project) {
        // Inertia PUT with files requires _method spoofing via POST
        data.append("_method", "PUT");
        router.post(route("projects.update", props.project.id), data, options);
    } else {
        router.post(route("projects.store"), data, options);
    }
};
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.2s ease;
}
.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
</style>
