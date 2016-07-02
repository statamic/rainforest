module.exports = {
    template: require('./time.template.html'),

    props: ['data', 'config', 'name'],

    computed: {
        hour: {
            set: function(val) {
                this.ensureTime();
                var time = this.data.split(':');
                var hour = parseInt(val);

                // ensure you cant go beyond the range
                hour = (hour > 23) ? 23 : hour;
                hour = (hour < 0) ? 0 : hour;

                time[0] = this.pad(hour);
                this.data = time.join(':');
            },
            get: function() {
                return (this.hasTime) ? this.pad(this.data.split(':')[0]) : '';
            }
        },

        minute: {
            set: function(val) {
                this.ensureTime();
                var time = this.data.split(':');
                var minute = parseInt(val);

                // ensure you cant go beyond the range
                minute = (minute > 59) ? 59 : minute;
                minute = (minute < 0) ? 0 : minute;

                time[1] = this.pad(minute);
                this.data = time.join(':');
            },
            get: function() {
                return (this.hasTime) ? this.pad(this.data.split(':')[1]) : '';
            }
        },

        hasTime: function() {
            return this.data !== null;
        }
    },

    methods: {
        pad: function(val) {
            return ('00' + val).substr(-2, 2);
        },

        ensureTime: function() {
            if (! this.hasTime) {
                this.initializeTime();
            }
        },

        initializeTime: function() {
            this.data = '00:00';
        },

        clear: function() {
            this.data = null;
        },

        incrementHour: function(val) {
            this.ensureTime();

            var hour = parseInt(this.hour) + val;

            // enable wrapping
            hour = (hour === 24) ? 0 : hour;
            hour = (hour === -1) ? 23 : hour;

            this.hour = hour;
        },

        incrementMinute: function(val) {
            this.ensureTime();

            var minute = parseInt(this.minute) + val;

            // enable wrapping
            minute = (minute === 60) ? 0 : minute;
            minute = (minute === -1) ? 59 : minute;

            this.minute = minute;
        },

        focusMinute: function() {
            $(this.$els.minute).focus().select();
        }
    }
};
