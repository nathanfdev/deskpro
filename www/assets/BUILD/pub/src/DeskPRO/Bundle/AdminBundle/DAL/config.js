export const repositoriesConfig = {
  Person:        { type: 'api', url: '/people', allowAll: false },
  TwilioAccount: { type: 'api', url: '/twilio_accounts', allowAll: true },
  TwilioNumber:  { type: 'api', url: '/twilio_numbers', allowAll: true },
  TwilioQueue:   { type: 'api', url: '/twilio_queues', allowAll: true }
};

export default repositoriesConfig;
