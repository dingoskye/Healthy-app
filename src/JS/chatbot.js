// Client voor ai-testing.php — gebruikt serverproxy api/chat.php
(function () {
    const state = {
        history: [
            {
                role: 'system',
                content:
                    'you are a high-intelligence nutrition coach. ' +
                    'You are cheerful and ready to help your user in giving nutrition advice based on their needs. ' +
                    'You only give nutrition advice, if the user asks for something else you kindly redirect towards the topic of nutrition. ' +
                    'Assume that user has no knowledge on the nutrition facts of their food. Make accurate estimates based on the dish names given.' +
                    'If there is an issue that is too dangerous for an AI bot to handle, refer the user to contact a professional for advice/suggestions.'

            },
        ],
    };

    const els = {
        messages: document.getElementById('messages'),
        input: document.getElementById('input'),
        sendBtn: document.getElementById('sendBtn'),
        model: document.getElementById('model'),
    };

    function bubbleClass(role) {
        // Stijlen gematcht met je oude design (user/assistant kleuren)
        const base = 'max-w-[80%] px-3 py-2 rounded-[14px] leading-relaxed whitespace-pre-wrap break-words border';
        if (role === 'user') {
            return `${base} self-end bg-[#263159] border-[#2e3c7a]`;
        } else if (role === 'assistant') {
            return `${base} self-start bg-[#1f2937] border-[#273244]`;
        }
        return `${base} self-center opacity-80 text-xs`;
    }

    function addMessage(role, content) {
        const div = document.createElement('div');
        div.className = bubbleClass(role);
        div.textContent = content;
        els.messages.appendChild(div);
        els.messages.scrollTop = els.messages.scrollHeight;
        return div;
    }

    function setLoading(loading) {
        els.sendBtn.disabled = loading;
        els.sendBtn.innerHTML = loading
            ? '<span class="inline-block w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin align-middle"></span>'
            : 'Send';
    }

    async function sendMessage(text) {
        if (!text.trim()) return;
        addMessage('user', text);
        state.history.push({ role: 'user', content: text });
        els.input.value = '';
        setLoading(true);

        try {
            const res = await fetch('api/chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',        // <-- voeg dit toe
                body: JSON.stringify({ model: els.model.value, messages: state.history }),
            });

            if (!res.ok) throw new Error('Proxy error: ' + res.status + ' ' + res.statusText);
            const data = await res.json();
            const reply = data.reply || '(No response)';
            addMessage('assistant', reply);
            state.history.push({ role: 'assistant', content: reply });
        } catch (err) {
            console.error(err);
            addMessage('assistant', '⚠️ ' + (err.message || 'Something went wrong'));
        } finally {
            setLoading(false);
        }
    }

    els.sendBtn.addEventListener('click', () => sendMessage(els.input.value));
    els.input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage(els.input.value);
        }
    });

    // Greet
    addMessage('assistant', 'Hi! I am Nutribot! I am an AI coach, here to help you with your nutrition needs!');
})();

//mapangpang