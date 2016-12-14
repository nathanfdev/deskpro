export const repositoriesConfig = {
  Person:             { type: 'api', url: '/people', allowAll: false },
  VoiceAccount:       { type: 'api', url: '/voice_accounts', allowAll: true },
  VoiceNumber:        { type: 'api', url: '/voice_numbers', allowAll: true },
  VoiceQueue:         { type: 'api', url: '/voice_queues', allowAll: true },
  VoiceAutoAttendant: { type: 'api', url: '/voice_auto_attendants', allowAll: true },
  VoiceAsset:         { type: 'api', url: '/voice_assets', allowAll: true }
};

export default repositoriesConfig;
