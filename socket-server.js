const io = require('socket.io')(6001, {
    cors: {
        origin: '*',
    }
});

console.log('Socket.io server running on port 6001');

io.on('connection', socket => {
    console.log('Client connected:', socket.id);

    socket.on('chat:message', data => {
        console.log('Message received:', data);
        io.emit('chat:message', data); // broadcast to everyone
    });

    socket.on('disconnect', () => {
        console.log('Client disconnected:', socket.id);
    });
});
