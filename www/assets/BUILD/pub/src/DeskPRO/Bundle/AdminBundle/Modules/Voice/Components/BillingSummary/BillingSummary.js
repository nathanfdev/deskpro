import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';
import classNames from 'classnames';
import moment from 'moment';
import { Select } from 'DeskPRO/Component/Semantic/ReactForm';
import SectionHeader from '../../../Common/Components/SectionHeader';
import AccountChoiceWrapper from '../Common/AccountChoiceWrapper';

const twilioStatGroups = [
  {
    category: 'phonenumbers',
    title:    'Phone Numbers',
    children: [
      {
        category: 'phonenumbers-tollfree',
        title:    'Toll Free PhoneNumbers'
      },
      {
        category: 'phonenumbers-mobile',
        title:    'Mobile PhoneNumbers'
      },
      {
        category: 'phonenumbers-local',
        title:    'Local PhoneNumbers'
      },
    ]
  },
  {
    title:    'Programmable Voice',
    children: [
      {
        category: 'calls',
        title:    'Voice Minutes',
        children: [
          {
            category: 'calls-inbound',
            title:    'Inbound Voice Minutes',
            children: [
              {
                category: 'calls-inbound-tollfree',
                title:    'Inbound Toll Free Calls'
              },
              {
                category: 'calls-inbound-local',
                title:    'Inbound Local Calls'
              },
              {
                category: 'calls-inbound-mobile',
                title:    'Inbound Mobile Calls'
              },
            ]
          },
          {
            category: 'calls-outbound',
            title:    'Outbound Voice Minutes'
          }
        ]
      },
      {
        category: 'calls-sip',
        title:    'SIP Minutes',
        children: [
          {
            category: 'calls-sip-inbound',
            title:    'Inbound SIP Minutes',
          },
          {
            category: 'calls-sip-outbound',
            title:    'Outbound SIP Minutes',
          }
        ]
      },
      {
        category: 'calls-client',
        title:    'Twilio Client Minutes'
      },
      {
        category: 'calls-recordings',
        title:    'Call Recordings'
      },
      {
        category: 'calls-globalconference',
        title:    'Conference Minutes'
      },
      {
        category: 'transcriptions',
        title:    'Transcriptions'
      }
    ]
  },
  {
    title:    'Add-ons',
    children: [
      {
        category: 'marketplace-voicebase-transcription',
        title:    'VoiceBase Transcription'
      }
    ]
  }
];

class BillingSummary extends React.Component {

  static propTypes = {
    accounts:      PropTypes.object,
    account:       PropTypes.object,
    date:          PropTypes.string,
    records:       PropTypes.object,
    changeAccount: PropTypes.func,
    changeDate:    PropTypes.func
  };

  render() {
    const { account, accounts, records, date, changeAccount, changeDate } = this.props;
    const dateChoices = [];

    if (account) {
      const fromDate = moment(account.get('date_created'));
      let now = moment();

      while (fromDate < now) {
        dateChoices.push(({
          value: `01-${now.format('MM')}-${now.format('YYYY')}`,
          label: `${now.format('MMM')} ${now.format('YYYY')}`
        }));

        now = now.subtract(1, 'month');
      }
    }

    return (
      <div className="page">
        <SectionHeader
          title="Billing Summary"
          dividing
        />

        <div className="billing-summary">
          <div className="billing-summary-form">
            {accounts.size > 1 &&
            <div className="inline-field account">
              <AccountChoiceWrapper accounts={accounts}>
                <Select
                  clearable={false}
                  value={account.get('id')}
                  onChange={changeAccount}
                />
              </AccountChoiceWrapper>
            </div>
            }

            <div className="inline-field date">
              <Select
                clearable={false}
                choices={dateChoices}
                value={date}
                onChange={changeDate}
              />
            </div>
          </div>

          <DeskproBillingGroup
            stat={records.get('deskpro_stat')}
            groups={[twilioStatGroups[0]]}
            records={records.get('provider_stat_records')}
          />

          {account.get('account_id') !== '__ACCOUNT_ID__' && records.get('provider_stat_records') &&
          <ProviderBillingGroup
            groupTitle="Twilio"
            groups={twilioStatGroups}
            records={records.get('provider_stat_records')}
          />}
        </div>
      </div>
    );
  }
}

class BaseProviderBillingGroup extends React.Component {

  static propTypes = {
    records: PropTypes.array
  };

  getRecord = category =>
    this.props.records.filter(r => r.get('category') === category).first() || Immutable.fromJS({});

  getPrice = (group) => {
    const record = this.getRecord(group.category);

    let price = parseFloat(0);
    if (group.children) {
      group.children.forEach((childGroup) => {
        price += parseFloat(this.getPrice(childGroup));
      });
    } else if (record) {
      price = parseFloat(record.get('price'));
    }

    return price.toFixed(3);
  };

  getPriceUnit = (group) => {
    const record = this.getRecord(group.category);

    if (group.children) {
      return this.getPriceUnit(group.children[0]);
    } else if (record) {
      return record.get('price_unit');
    }

    return '';
  };

  getCount = (group) => {
    const record = this.getRecord(group.category);

    let count = 0;
    if (group.children) {
      group.children.forEach((childGroup) => {
        count += parseInt(this.getCount(childGroup), 10);
      });
    } else if (record) {
      count = parseInt(record.get('count'), 10);
    }

    return count;
  };

  getCountUnit = (group) => {
    const record = this.getRecord(group.category);

    if (group.children) {
      return this.getCountUnit(group.children[0]);
    } else if (record) {
      return record.get('count_unit');
    }

    return '';
  };

  formatPrice = (group) => {
    const price = this.getPrice(group);
    const priceUnit = this.getPriceUnit(group);

    if (priceUnit === 'usd') {
      return `$${price}`;
    }

    return `${price} ${priceUnit}`;
  };

  formatCount = (group) => {
    const count = this.getCount(group);
    const countUnit = this.getCountUnit(group);

    return `${count} ${countUnit}`;
  };
}

class DeskproBillingGroup extends BaseProviderBillingGroup {

  static propTypes = {
    stat:   PropTypes.object,
    groups: PropTypes.array
  };

  render() {
    const { stat, groups } = this.props;

    return (
      <div className="billing-summary-group">
        <div className="group-header">
          <div className="group-info">
            <div className="col-5">
              <span className="title">
                Deskpro
              </span>
            </div>
            <div className="col-1" />
            <div className="col-1 align-right">
              Total:
            </div>
            <div className="col-1">
              <span className="price">
                ${stat.get('total_calls_price')}
              </span>
            </div>
          </div>
        </div>
        <div className="group-body">
          <div className="group-row">
            <div className="group-info">
              <div className="col-5">
                <span className="title">
                  Calls
                </span>
              </div>
              <div className="col-1">
                <span className="count">
                  {stat.get('total_calls_count')} calls
                </span>
              </div>
              <div className="col-1" />
              <div className="col-1">
                <span className="price">
                  ${stat.get('total_calls_price')}
                </span>
              </div>
            </div>
            {groups.map(group =>
              <div className="group-info">
                <div className="col-5">
                  <span className="title">
                    {group.title}
                  </span>
                </div>
                <div className="col-1">
                  <span className="count">
                    {this.formatCount(group)}
                  </span>
                </div>
                <div className="col-1" />
                <div className="col-1">
                  <span className="price">
                    {this.formatCount(group)}
                  </span>
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
    );
  }
}

class ProviderBillingGroup extends BaseProviderBillingGroup {

  static propTypes = {
    groupTitle: PropTypes.string,
    groups:     PropTypes.array
  };

  renderStatGroup = (statGroup, level = 0) => {
    const titleClasses = { 'has-children': statGroup.children };
    titleClasses[`level-${level}`] = level > 0;

    return (
      <div className="group-row">
        <div className="group-info">
          <div className="col-5">
            <span className={classNames('title', titleClasses)}>
              {statGroup.title}
            </span>
          </div>
          <div className="col-1">
            <span className="count">
              {this.formatCount(statGroup)}
            </span>
          </div>
          <div className="col-1" />
          <div className="col-1">
            <span className="price">
              {this.formatPrice(statGroup)}
            </span>
          </div>
        </div>
        {statGroup.children &&
        <div className="row-children">
          {statGroup.children.map(childGroup => this.renderStatGroup(childGroup, level + 1))}
        </div>}
      </div>
    );
  };

  render() {
    const { groupTitle, groups = [] } = this.props;

    return (
      <div className="billing-summary-group">
        <div className="group-header">
          <div className="group-info">
            <div className="col-5">
              <span className="title">
                {groupTitle}
              </span>
            </div>
            <div className="col-1" />
            <div className="col-1 align-right">
              Total:
            </div>
            <div className="col-1">
              <span className="price">
                {this.formatPrice({ children: twilioStatGroups })}
              </span>
            </div>
          </div>
        </div>
        {groups.length > 0 &&
        <div className="group-body">
          {groups.map(group => this.renderStatGroup(group))}
        </div>}
      </div>
    );
  }
}

export default BillingSummary;
