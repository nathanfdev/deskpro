import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export function shortTimeAgoFormatter(value, unit) {
  switch (unit) {
    case 'second':
      return `${value} ${portalPhrases.get('helpcenter.duration_short.seconds')}`;
    case 'minute':
      return `${value} ${portalPhrases.get('helpcenter.duration_short.minutes')}`;
    case  'hour':
      return `${value} ${portalPhrases.get('helpcenter.duration_short.hours')}`;
    case  'day':
      return `${value} ${portalPhrases.get('helpcenter.duration_short.days')}`;
    case  'week':
      return `${value} ${portalPhrases.get('helpcenter.duration_short.weeks')}`;
    case  'month':
      return `${value} ${portalPhrases.get('helpcenter.duration_short.months')}`;
    case  'year':
      return `${value} ${portalPhrases.get('helpcenter.duration_short.years')}`;
    default:
      return '';
  }
}

export function timeAgoFormatter(value, unit, suffix) {
  if (unit === 'second') {
    return 'a moment ago';
  }

  return `${value} ${value !== 1 ? (`${unit}s`) : unit} ${suffix}`;
}
