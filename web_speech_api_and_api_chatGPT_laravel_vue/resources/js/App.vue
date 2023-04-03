<template>
    <div class="chat" :class="loading ? 'loading' : ''">

        <div class="loading-placeholder">
            Лох думает...
        </div>

        <div v-for="message in messages"
            :class="'message-' + message.from"
        >{{ message.text }}</div>

        <div class="bottom">
            <textarea v-model="ask"></textarea>
            <button @click="request(ask)">Send request</button>
            <button @click="phrases">Listen</button>
        </div>
    </div>
</template>

<script>
export default {
    name: "App",
    data() {
        return {
            ask: '',
            messages: [],
            loading: false
        }
    },
    methods: {
        request(ask)
        {
            this.loading = true
            axios
                .post('/api/test', {
                    ask: ask
               })
                .then(response => {
                    this.speak(response.data.choices[0].message.content)
                    this.messages.push({from: 'gpt', text: response.data.choices[0].message.content})
                    this.loading = false

                }).catch(() => {
                this.speak('Извините ошибка реквеста')
                this.messages.push({from: 'gpt', text: 'Извините ошибка реквеста'})
                this.loading = false

            });
        },

        phrases()
        {
            // Создаем распознаватель
            const recognizer = new webkitSpeechRecognition();

            // Какой язык будем распознавать?
            recognizer.continuous = true;

            // Используем колбек для обработки результатов
            recognizer.onresult = (event) => {
                const result = event.results[event.resultIndex];
                if (result.isFinal) {
                    console.log('распознано:  ', result[0].transcript, 'точность: '+ event.results[0][0].confidence)
                    if (result[0].transcript.indexOf('лох') !== -1 && result[0].transcript.indexOf('лох') <= 5) {
                        console.log('команда: ', result[0].transcript)
                        this.ask = result[0].transcript.replace('лох', '')
                        this.request(this.ask)
                        this.messages.push({from: 'me', text: this.ask})
                    }
                } else {
                    console.log('Промежуточный результат: ', result[0].transcript);
                }
            };

            // Начинаем слушать микрофон и распознавать голос
            recognizer.start();

            // ловим ошибки
            recognizer.onerror = (event) => {
                console.error(`Speech recognition error detected: ${event.error}`);
                console.log('Additional information: ' + event.message);
            };

            // если стопается заново запускаем распознавание
            recognizer.onend = (event) => {
                console.log("Speech recognition service disconnected");
                recognizer.start();
           };
        },

        speak(text)
        {
            const utterance = new SpeechSynthesisUtterance(text);

            speechSynthesis.speak(utterance);
        },
    }
}
</script>

<style scoped>
.chat {
    width: 700px;
    margin: 0 auto;
    background-color: #f1f1f1;
}
.message-me {
    padding: 10px;
    padding-left: 100px;
    text-align: right;
}
.message-gpt {
    padding: 10px;
    padding-right: 100px;
}
.loading {
    opacity: 0.5;
}
.loading .loading-placeholder {
    display: block;
}
.loading-placeholder {
    text-align: center;
    display: none;
    position: absolute;
    z-index: 999;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    padding-top: 50vh;
    background-color: #8b8b8b52;
}
</style>