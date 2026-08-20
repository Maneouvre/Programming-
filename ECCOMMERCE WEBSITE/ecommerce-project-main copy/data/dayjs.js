// Day.js Clean Local ESM Build (No external dependencies, No variables missing)

const SECONDS_MS = 1000;
const MINUTES_MS = 60000;
const HOURS_MS = 3600000;
const DAYS_MS = 86400000;

class Dayjs {
  constructor(dateInput) {
    this.$d = this.parseDate(dateInput);
    this.init();
  }

  parseDate(input) {
    if (input instanceof Date) return new Date(input.getTime());
    if (typeof input === 'string' || typeof input === 'number') return new Date(input);
    if (input && input.$d) return new Date(input.$d.getTime()); // clone if another dayjs object
    return new Date();
  }

  init() {
    this.$y = this.$d.getFullYear();
    this.$M = this.$d.getMonth(); // 0-11
    this.$D = this.$d.getDate();
    this.$W = this.$d.getDay();
    this.$H = this.$d.getHours();
    this.$m = this.$d.getMinutes();
    this.$s = this.$d.getSeconds();
  }

  isValid() {
    return !isNaN(this.$d.getTime());
  }

  // Implementation of .add() used for delivery dates
  add(number, unit) {
    const value = Number(number);
    const cleanUnit = String(unit).toLowerCase();
    let timeOffset = 0;

    if (cleanUnit === 'day' || cleanUnit === 'days' || cleanUnit === 'd') {
      timeOffset = value * DAYS_MS;
    } else if (cleanUnit === 'week' || cleanUnit === 'weeks' || cleanUnit === 'w') {
      timeOffset = value * DAYS_MS * 7;
    } else if (cleanUnit === 'hour' || cleanUnit === 'hours' || cleanUnit === 'h') {
      timeOffset = value * HOURS_MS;
    } else if (cleanUnit === 'minute' || cleanUnit === 'minutes' || cleanUnit === 'm') {
      timeOffset = value * MINUTES_MS;
    } else if (cleanUnit === 'month' || cleanUnit === 'months' || cleanUnit === 'm') {
      const newDate = new Date(this.$d.getTime());
      newDate.setMonth(newDate.getMonth() + value);
      return new Dayjs(newDate);
    } else if (cleanUnit === 'year' || cleanUnit === 'years' || cleanUnit === 'y') {
      const newDate = new Date(this.$d.getTime());
      newDate.setFullYear(newDate.getFullYear() + value);
      return new Dayjs(newDate);
    }

    return new Dayjs(new Date(this.$d.getTime() + timeOffset));
  }

    format(formatStr) {
    const str = formatStr || 'YYYY-MM-DDTHH:mm:ssZ';
    
    // Arrays for full text conversions completely offline
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    const matches = {
      YYYY: String(this.$y),
      YY: String(this.$y).slice(-2),
      MMMM: months[this.$M], // Full month text (e.g., June)
      MM: String(this.$M + 1).padStart(2, '0'),
      M: String(this.$M + 1),
      DD: String(this.$D).padStart(2, '0'),
      D: String(this.$D),
      dddd: days[this.$W] // Full day text (e.g., Monday)
    };

    // Sorted by longest token length first to avoid partial replacement bugs
    return str.replace(/YYYY|YY|MMMM|MM|M|dddd|DD|D/g, (match) => matches[match] || match);
  }


  toString() {
    return this.$d.toUTCString();
  }
}

// Wrapper function to mirror original API: dayjs()
const dayjsWrapper = function(dateInput) {
  return new Dayjs(dateInput);
};

dayjsWrapper.isDayjs = function(obj) {
  return obj instanceof Dayjs;
};

export default dayjsWrapper;
