module.exports = {

    template: require('./date.template.html'),

    props: {
        name: String,
        data: {},
        config: { default: function() { return {}; } }
    },

    data: function() {
        return {
            time: null
        }
    },

    computed: {
        hasDate: function() {
            if (this.blankAllowed) {
                return this.data !== null;
            } else {
                return true;
            }
        },

        hasTime: function() {
            return this.data && this.data.length > 10;
        },

        timeAllowed: function() {
            return this.config.allow_time !== false;
        },

        blankAllowed: function() {
            return this.config.allow_blank === true;
        }
    },

    methods: {

        /**
         * Return the date string.
         * `this.data` is the full datetime string. This will get just the date.
         */
        dateString: function() {
            if (this.data) {
                return this.data.substr(0, 10)
            } else {
                return moment().format('YYYY-MM-DD')
            }
        },

        /**
         * Updates the date string
         */
        updateDateString: function(dateString) {
            var timeString = (this.hasTime) ? ' ' + this.time : '';

            this.data = dateString + timeString;
        },

        /**
         * Create a watcher for the `this.time` variable.
         * Whenever the time value is updated we want to tack it onto the end
         * of the date string. Or just remove the time if it's null.
         */
        watchTime: function() {
            var self = this;

            this.$watch('time', function(newTime, oldTime) {
                if (newTime === null) {
                    self.data = self.dateString();
                } else {
                    self.data = self.dateString() + ' ' + newTime;
                }
            });
        },

        addTime: function() {
            this.time = moment().format('HH:mm');

            this.$nextTick(function() {
                $(this.$refs.time.$els.hour).focus().select();
            });
        },

        removeTime: function() {
            this.time = null;
        },

        addDate: function() {
            this.data = moment().format('YYYY-MM-DD');
            this.$nextTick(function() {
                this.bindCalendar();
            });
        },

        removeDate: function() {
            this.data = null;
        },

        bindCalendar: function() {
            var self = this;

            // Use the date if there is one, otherwise use today's date.
            var date = (this.data)
                ? moment(self.dateString())
                : moment().format('YYYY-MM-DD');

            new Calendar({
                element: $(self.$el).find('.daterange'),
                current_date: moment(date),
                earliest_date: this.config.earliest_date || "January 1, 1900",
                callback: function() {
                    var newDate = moment(this.current_date).format('YYYY-MM-DD');
                    self.updateDateString(newDate);
                }
            });
        }

    },

    ready: function() {
        if (this.data) {
            this.time = this.data.substr(11);
        }

        // If there's no data (ie. a blank field) and blanks are _not_ allowed, we want
        // to initialize the data to the current date, so that the value will get
        // saved without the user needing to interact with the field first.
        if (!this.data && !this.blankAllowed) {
            this.data = moment().format('YYYY-MM-DD');
        }

        this.watchTime();
        this.bindCalendar();
    }
};
