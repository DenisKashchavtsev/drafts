<template>
    <div>
        <label>userSignal:</label><br/>
        <textarea id="yourId" v-model="userSignal"></textarea><br/>

        <div class="video-container" style="display: flex; justify-content: center; align-content: center">
            <div class="block_video">
                <video class="my-video" autoplay playsinline controls="false" :srcObject.prop="userStream" />

<!--                <button @click="stopMicrophone" style="position: absolute;bottom: -22px;">Turn off Microphone</button>-->
<!--                <button @click="startMicrophone" style="position: absolute;bottom: -22px;right: 0">Turn on Microphone</button>-->

                <div class="user_info">
                    Me: {{ user.name }}, {{ user.email }}
                </div>
            </div>
            <div>
                <p><button @click="startShareScreen">Start share screen</button>
                <button @click="stopShareScreen">Stop share screen</button></p>

                <p><button @click="startRecorder()">Start recording</button>
                    <button @click="stopRecorder()">Stop recording</button></p>
            </div>

            <div v-for="partner in users" class="block_video">
                <template v-if="partner && partner.id !== user.id">
                    <video class="user-video" autoplay playsinline controls="false" :srcObject.prop="partner.stream" />
                    <div class="user_info">
                        Partner: {{ partner.name }}, {{ partner.email }}
                    </div>
                </template>
            </div>
        </div>

    </div>
</template>

<script>
import Peer from 'simple-peer';
import WebRecorder from "./webRecorder/WebRecorder";

export default {
    name: "app",
    data() {
        return {
            userStream: null,
            partnerStreams: [],

            userSignal: '',

            signals: [],

            peer: null,

            user: window.user,
            users: [],

            recorder: null,
            recordedChunks: [],
        }
    },
    async created() {
        this.listenPeers()

        await this.getUserStream()

        this.initPeer()

        this.recorder = new WebRecorder();
    },
    watch: {
        // signals: function(){
        //     this.signals.forEach(signal => {
        //         if(signal.signal !== this.userSignal && !this.connected.includes(signal.signal)) {
        //             this.peer.signal(signal.signal);
        //             this.connected.push(JSON.parse(signal.signal))
        //             console.log('watch')
        //         }
        //     });
        // }
    },
    methods: {
        // слушаем каналы
        listenPeers(){
            console.log('listenPeers')

            window.Echo.join('peer')
                .here((users) => {
                    console.log('here',users); // Получаем список всех юзеров
                    this.users = users
                })
                .joining((user) => {
                    console.log('joining', user.name); //  Кто-то подключился к каналу, можно сделать его 'online'
                })
                .leaving((user) => {
                    console.log('leaving',user.name); // Кто-то вышел из канала, можно сделать его 'offline'
                });

            window.Echo.private('peer.' + this.user.id)
                .listen('UserPeerEvent', (e) => {
                    console.log('===-'+e.data.type,e.data.signal.substring(0, 20))
                    if(e.data.type === 'call') {
                        this.answer(e.data.signal, e.data.from_user_id)
                    } else if (e.data.type === 'answer') {
                        this.accept(e.data.signal, e.data.from_user_id);
                    }
                });
        },
        listenUserPeer(){
            // window.Echo.channel('peer-'+this.userId)
            //     .listen('UserPeerEvent', ( { data } ) => {
            //         if(data.type === 'call') {
            //             this.answer(data)
            //         } else if (data.type === 'answer') {
            //             console.log('===call',data.signal)
            //             this.peer.signal(data.signal);
            //
            //             this.peer.on('stream', (stream) => {
            //                 this.partnerStream = stream
            //             })
            //         }
            //     })
        },

        // получаем стрим текущего юзера
        async getUserStream() {
            this.userStream = await navigator.mediaDevices.getUserMedia({video: true, audio: true})
        },

        // инициализируем текущую точку
        initPeer() {
            this.peer = new Peer({
                initiator: true,
                trickle: false,
                stream: this.userStream
            })

            this.peer.on('error', err => console.log('error', err))

            this.peer.on('signal', (data) => {
                this.userSignal = JSON.stringify(data)

                this.users.forEach(user => {
                    if (user.id !== this.user.id) {
                        this.call(user.id)
                    }
                })
            })
        },

        call(user_id) {
            axios.post('call', {
                type: 'call',
                user_id: user_id,
                from_user_id: this.user.id,
                signal: this.userSignal
            })
        },

        async answer(signal, user_id) {
                this.peer = new Peer({
                    initiator: false,
                    trickle: false,
                    stream: this.userStream
                })

                this.peer.on('error', err => console.log('error', err))

                await this.peer.on('signal', (data) => {
                    let userSignal = JSON.stringify(data)

                    axios.post('answer', {
                        type: 'answer',
                        user_id: user_id,
                        from_user_id: this.user.id,
                        signal: userSignal
                    })
                })

                this.peer.on('stream', (stream) => {
                    this.users.forEach(user => {
                        if (user.id === user_id) {
                            user.stream = stream;
                        }
                    })
                })

                this.peer.signal(signal);
        },

        accept(signal, user_id) {
            this.peer.signal(signal);

            this.peer.on('stream', (stream) => {
                this.users.forEach(user => {
                    if (user.id === user_id) {
                        user.stream = stream;
                    }
                })
            })
        },

        startShareScreen() {
            navigator.mediaDevices.getDisplayMedia({ cursor: true }).then(stream => {
                this.recorder.replaceVideoTrack(stream.getVideoTracks()[0]);
                this.userStream = stream
                console.log('startShareScreen')
            }).catch(err => {
                throw new Error(`Unable to fetch stream ${err}`);
            })
        },

        stopShareScreen() {
            navigator.mediaDevices.getUserMedia({video: true, audio: true}).then(stream => {
                this.recorder.replaceVideoTrack(stream.getVideoTracks()[0]);
                this.userStream = stream
                console.log('stopShareScreen')
            }).catch(err => {
                throw new Error(`Unable to fetch stream ${err}`);
            })
        },

        async startRecorder() {
            this.recorder.start(this.userStream)
            console.log('startRecorder', this.recorder)
        },

        async stopRecorder() {
            await this.recorder.download('example.webm');
        },

        stopMicrophone() {
            console.log(this.userStream.getTracks()[0])
            this.userStream.getTracks()[0].stop();
        },
        startMicrophone() {
            this.userStream.getTracks()[0].enabled();
        }
    }
}
</script>

<style scoped>
.video-container {
    border: 2px solid #cccccc;
    position: relative;
    box-shadow: 1px 1px 11px #9e9e9e;
}

.my-video {
    height: 380px;
    width: 380px;
    border: 6px solid #92fa56;
    border-radius: 6px;
    background-color: black;
}

.user-video {
    height: 380px;
    width: 380px;
    border: 6px solid #003aff;
    border-radius: 6px;
    background-color: black;
}

.block_video {
    position: relative;
    float: left;
}

.user_info {
    position: absolute;
    z-index: 2;
    bottom: 15px;
    left: 15px;
    color: white;
}

</style>