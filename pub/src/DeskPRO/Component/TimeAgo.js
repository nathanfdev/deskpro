import React, { PropTypes } from 'react';

export class TimeAgo extends React.Component {

  static propTypes = {
    live: PropTypes.bool.isRequired,
    minPeriod: PropTypes.number.isRequired,
    maxPeriod: PropTypes.number.isRequired,
    component: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.func
    ]).isRequired,
    formatter: PropTypes.func.isRequired,
    date: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.number,
      PropTypes.instanceOf(Date)
    ]).isRequired
  };

  static defaultProps = {
    live: true,
    component: 'span',
    minPeriod: 0,
    maxPeriod: Infinity,
    formatter: (value, unit, suffix) => {
      return value + ' ' + (value !== 1 ? (unit + 's') : unit) + ' ' + suffix;
    }
  };

  constructor(props) {
    super(props);
    this.timeoutId = 0;
  }

  componentDidMount() {
    this.mounted = true;
    if (this.props.live) {
      this.tick(true);
    }
  }

  componentDidUpdate(lastProps) {
    const { live } = this.props;

    if (live !== lastProps.live || date !== lastProps.date) {
      if (!live && this.timeoutId) {
        clearTimeout(this.timeoutId);
        this.timeoutId = undefined;
      }

      this.tick();
    }
  }

  componentWillUnmount() {
    this.mounted = false;

    if (this.timeoutId) {
      clearTimeout(this.timeoutId);
      this.timeoutId = undefined;
    }
  }

  tick(refresh) {
    const { live, date, minPeriod, maxPeriod } = this.props;
    if (!this.mounted || !live) {
      return;
    }

    const then = (new Date(date)).valueOf();
    const now = Date.now();
    const seconds = Math.round(Math.abs(now - then) / 1000);

    let period = 1000;
    if (seconds < 60) {
      period = 1000;
    } else if (seconds < 60 * 60) {
      period = 1000 * 60;
    } else if (seconds < 60 * 60 * 24) {
      period = 1000 * 60 * 60;
    } else {
      period = 0;
    }

    period = Math.min(Math.max(period, minPeriod), maxPeriod);

    if (!!period) {
      this.timeoutId = setTimeout(this.tick, period);
    }
    if (!refresh) {
      this.forceUpdate();
    }
  }

  render() {
    const { date, component, formatter } = this.props;
    const props = this.props;

    const then = (new Date(date)).valueOf();
    const now = Date.now();
    const seconds = Math.round(Math.abs(now - then) / 1000);
    const suffix = then < now ? 'ago' : 'from now';

    let value;
    let unit;

    if (seconds < 60) {
      value = Math.round(seconds);
      unit = 'second';
    } else if (seconds < 60 * 60) {
      value = Math.round(seconds / 60);
      unit = 'minute';
    } else if (seconds < 60 * 60 * 24) {
      value = Math.round(seconds / (60 * 60));
      unit = 'hour';
    } else if (seconds < 60 * 60 * 24 * 7) {
      value = Math.round(seconds / (60 * 60 * 24));
      unit = 'day';
    } else if (seconds < 60 * 60 * 24 * 30) {
      value = Math.round(seconds / (60 * 60 * 24 * 7));
      unit = 'week';
    } else if (seconds < 60 * 60 * 24 * 365) {
      value = Math.round(seconds / (60 * 60 * 24 * 30));
      unit = 'month';
    } else {
      value = Math.round(seconds / (60 * 60 * 24 * 365));
      unit = 'year';
    }

    const newProps = {...props};

    delete newProps.date;
    delete newProps.formatter;
    delete newProps.component;

    return React.createElement(component, newProps, formatter(value, unit, suffix, then));
  }
}
