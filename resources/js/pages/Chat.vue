<script setup lang="ts">
import { useEcho } from '@laravel/echo-vue';
import { router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

interface Message {
    username: string;
    message: string;
    timestamp: string;
}

const messages = ref<Message[]>([]);
const username = ref('');
const messageText = ref('');
const messagesContainer = ref<HTMLElement | null>(null);

// Subscribe to chat channel with useEcho
const { leave } = useEcho<{ username: string; message: string }, 'reverb', 'public'>(
    'chat',
    '.message.sent',
    (data) => {
        messages.value.push({
            username: data.username,
            message: data.message,
            timestamp: new Date().toLocaleTimeString(),
        });

        // Auto-scroll to bottom
        setTimeout(() => {
            if (messagesContainer.value) {
                messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
            }
        }, 100);
    },
    [],
    'public' as const,
);

onMounted(() => {
    // Set default username
    username.value = `User${Math.floor(Math.random() * 1000)}`;
});

const sendMessage = () => {
    if (!messageText.value.trim() || !username.value.trim()) return;

    router.post(
        '/chat/send',
        {
            username: username.value,
            message: messageText.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                messageText.value = '';
            },
        },
    );
};

const handleKeyPress = (event: KeyboardEvent) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <div class="mx-auto max-w-4xl p-4">
            <!-- Header -->
            <div class="mb-6 text-center">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Simple Chat with Reverb</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Real-time chat using Laravel Reverb</p>
            </div>

            <!-- Chat Container -->
            <div class="overflow-hidden rounded-lg bg-white shadow-lg dark:bg-gray-800">
                <!-- Username Input -->
                <div class="border-b border-gray-200 p-4 dark:border-gray-700">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Username
                    </label>
                    <input
                        v-model="username"
                        type="text"
                        class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        placeholder="Enter your username"
                        maxlength="50"
                    />
                </div>

                <!-- Messages -->
                <div
                    ref="messagesContainer"
                    class="h-96 space-y-3 overflow-y-auto p-4 bg-gray-50 dark:bg-gray-900"
                >
                    <div
                        v-if="messages.length === 0"
                        class="flex h-full items-center justify-center text-gray-500 dark:text-gray-400"
                    >
                        No messages yet. Start the conversation!
                    </div>
                    <div
                        v-for="(msg, index) in messages"
                        :key="index"
                        class="rounded-lg bg-white p-3 shadow dark:bg-gray-800"
                    >
                        <div class="flex items-baseline justify-between">
                            <span class="font-semibold text-blue-600 dark:text-blue-400">
                                {{ msg.username }}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ msg.timestamp }}
                            </span>
                        </div>
                        <p class="mt-1 text-gray-800 dark:text-gray-200">{{ msg.message }}</p>
                    </div>
                </div>

                <!-- Message Input -->
                <div class="border-t border-gray-200 p-4 dark:border-gray-700">
                    <div class="flex gap-2">
                        <input
                            v-model="messageText"
                            type="text"
                            class="block flex-1 rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            placeholder="Type your message..."
                            maxlength="500"
                            @keypress="handleKeyPress"
                        />
                        <button
                            type="button"
                            class="rounded-md bg-blue-600 px-6 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 dark:focus:ring-offset-gray-800"
                            :disabled="!messageText.trim() || !username.trim()"
                            @click="sendMessage"
                        >
                            Send
                        </button>
                    </div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Press Enter to send, Shift+Enter for new line
                    </p>
                </div>
            </div>

            <!-- Instructions -->
            <div class="mt-6 rounded-lg bg-blue-50 p-4 dark:bg-gray-800">
                <h3 class="font-semibold text-blue-900 dark:text-blue-300">How to test:</h3>
                <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-blue-800 dark:text-blue-400">
                    <li>Open this page in multiple browser tabs/windows</li>
                    <li>Type a message and click Send</li>
                    <li>Watch messages appear in real-time across all tabs</li>
                    <li>Make sure Reverb server is running: <code class="bg-white px-1 py-0.5 rounded dark:bg-gray-700">php artisan reverb:start</code></li>
                </ul>
            </div>
        </div>
    </div>
</template>
