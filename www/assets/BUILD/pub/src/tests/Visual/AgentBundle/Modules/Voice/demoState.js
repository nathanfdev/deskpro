import avatar1 from '../../../Resources/avatars_people/b_obama.jpg';
import avatar2 from '../../../Resources/avatars_people/borat.jpg';
import avatar3 from '../../../Resources/avatars_people/eric_idle.jpg';
import avatar4 from '../../../Resources/avatars_people/kate_middleton.jpg';
import avatar5 from '../../../Resources/avatars_people/mathieu_kassovitz1.jpg';

const voiceState = {
  RecordsStore: {
    store: {
      Person: {
        agents: {
          loading: false,
          success: true
        },
        records: {
          1: {
            id:     '1',
            name:   'Bobby Steiner',
            avatar: {
              url_pattern: avatar1
            }
          },
          2: {
            id:            '2',
            name:          'Kristoffer Harris',
            primary_email: 'peter.gillingham@acme.com',
            avatar:        {
              url_pattern: avatar2
            }
          },
          3: {
            id:     '3',
            name:   'Nolan Kihn',
            avatar: {
              url_pattern: avatar3
            }
          },
          4: {
            id:     '4',
            name:   'Clifford Jenkins',
            avatar: {
              url_pattern: avatar4
            }
          },
          5: {
            id:     '5',
            name:   'Myrtice Schmidt',
            avatar: {
              url_pattern: avatar5
            }
          }
        },
        collections: {
          agents: ['1', '2', '3', '4', '5']
        }
      },
      VoiceQueue: {
        statuses: {
          all: {
            loading: false,
            success: true
          }
        },
        records: {
          1: {
            id:     '1',
            name:   'Queue 1',
            agents: ['1', '2', '3']
          },
          2: {
            id:   '2',
            name: 'Queue 2'
          },
          3: {
            id:   '3',
            name: 'Queue 3'
          },
          4: {
            id:   '4',
            name: 'Queue 4'
          },
          5: {
            id:   '5',
            name: 'Queue 5'
          }
        },
        collections: {
          all: ['1', '2', '3', '4', '5']
        }
      },
      VoiceNumber: {
        statuses: {
          all: {
            loading: false,
            success: true
          }
        },
        records: {
          1: {
            id:           '1',
            number:       '+18573665816',
            country_code: 'us'
          },
          2: {
            id:           '2',
            number:       '+18576655812',
            country_code: 'gb'
          }
        },
        collections: {
          all: ['1', '2']
        }
      }
    }
  }
};

export default voiceState;
