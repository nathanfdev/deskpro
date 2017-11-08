import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { CustomSelect, Radio, Checkbox, Input } from '@deskpro/react-components/lib/Components/Forms';
import { List, ListElement } from '@deskpro/react-components/lib/Components/Common';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';

@connect(state => ({
  me:         meSelector(state),
  agentTeams: allSelectorFactory('AgentTeam')(state)
}))
export class OwnershipSelectContainer extends React.Component {
  static propTypes = {
    me:                PropTypes.object,
    agentTeams:        PropTypes.object,
    selectedTeams:     PropTypes.instanceOf(Set).isRequired,
    existingTeams:     PropTypes.instanceOf(Set),
    isOwnershipGlobal: PropTypes.bool,
    onChange:          PropTypes.func,
  };

  static defaultProps = {
    existingTeams: new Set()
  };

  render() {
    const { me, agentTeams, onChange, selectedTeams, existingTeams, isOwnershipGlobal } = this.props;
    return (
      <OwnershipSelect
        me={me}
        agentTeams={agentTeams}
        selectedTeams={selectedTeams}
        existingTeams={existingTeams}
        isOwnershipGlobal={isOwnershipGlobal}
        onChange={onChange}
      />
    );
  }
}
export class OwnershipSelect extends React.Component {
  static propTypes = {
    me:                PropTypes.object,
    agentTeams:        PropTypes.object,
    selectedTeams:     PropTypes.instanceOf(Set),
    existingTeams:     PropTypes.instanceOf(Set),
    isOwnershipGlobal: PropTypes.bool,
    onChange:          PropTypes.func,
  };

  constructor(props) {
    super(props);
    let radio = '';
    if (this.props.isOwnershipGlobal) {
      radio = 'everyone';
    } else if (this.props.selectedTeams.size || this.props.existingTeams.size) {
      radio = 'specific';
    } else {
      radio = 'me';
    }
    this.state = {
      radio,
      filter: '',
    };
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.isOwnershipGlobal !== this.props.isOwnershipGlobal
      || nextProps.selectedTeams !== this.props.selectedTeams
      || nextProps.existingTeams !== this.props.existingTeams
    ) {
      let radio = '';
      if (nextProps.isOwnershipGlobal) {
        radio = 'everyone';
      } else if (nextProps.selectedTeams.size || nextProps.existingTeams.size) {
        radio = 'specific';
      } else {
        radio = 'me';
      }
      this.setState({
        radio,
      });
    }
  }

  shouldComponentUpdate(nextProps, nextState) {
    if (nextProps.agentTeams !== this.props.agentTeams) {
      return true;
    }
    if (nextProps.isOwnershipGlobal !== this.props.isOwnershipGlobal) {
      return true;
    }
    if (nextState.filter !== this.state.filter) {
      return true;
    }
    if (nextProps.existingTeams !== this.props.existingTeams) {
      return true;
    }
    return nextProps.selectedTeams !== this.props.selectedTeams;
  }

  onFilterChange = filter => this.setState({ filter });

  onRadioChange = (checked, value) => {
    this.setState({
      radio: value
    });
    if (value === 'everyone') {
      this.props.onChange(new Set(), true);
    } else if (value === 'me') {
      this.props.onChange(new Set(), false);
    }
    this.forceUpdate();
  };

  onCheckboxChange = (checked, value) => {
    const selectedTeams = new Set(this.props.selectedTeams);
    if (checked) {
      selectedTeams.add(value);
    } else {
      selectedTeams.delete(value);
    }
    this.props.onChange(selectedTeams, false, true);
    this.forceUpdate();
  };

  getSpecific = () => {
    const { me, selectedTeams, existingTeams } = this.props;
    const teams = [];
    const re = new RegExp(this.state.filter, 'i');
    this.props.agentTeams
      .filter(team => me.get('can_admin') || me.get('teams').indexOf(team.get('id')) !== -1)
      .forEach((team) => {
        if (!this.state.filter || team.get('name').match(re)) {
          teams.push(
            <ListElement key={team.get('id')}>
              <Checkbox
                value={team.get('id')}
                onChange={this.onCheckboxChange}
                checked={selectedTeams.has(team.get('id'))}
                existing={existingTeams.has(team.get('id'))}
              >
                {team.get('name')}
              </Checkbox>
            </ListElement>
        );
        }
      });
    return (
      <div>
        {this.props.agentTeams.size > 10 ?
          <Input
            placeholder={agentPhrases.get('agent.general.filter')}
            value={this.state.filter}
            className="teams_filter"
            onChange={this.onFilterChange}
          />
          : null }
        <List>
          {teams}
        </List>
      </div>
    );
  };

  inputRenderer = () => {
    const { selectedTeams, existingTeams, isOwnershipGlobal, agentTeams } = this.props;
    if (selectedTeams.size === 0 && existingTeams.size === 0) {
      return isOwnershipGlobal ? agentPhrases.get('agent.general.everyone') : agentPhrases.get('agent.general.just_me');
    } else if (selectedTeams.size <= 3 && selectedTeams.size > 0) {
      return agentTeams.filter(t => selectedTeams.has(t.get('id')))
        .map(o => o.get('name')).toArray().join(', ');
    } else if (selectedTeams.size === 0 && existingTeams.size > 0) {
      return agentPhrases.get('agent.general.select');
    }
    return `${selectedTeams.size} ${agentPhrases.get('agent.general.teams').toLowerCase()}`;
  };

  render() {
    const { me } = this.props;
    const { radio } = this.state;
    return (
      <CustomSelect
        inputRenderer={this.inputRenderer}
        displayInputWhenOpened={false}
      >
        <List>
          { (window.DESKPRO_PERSON_PERMS['agent_snippets.create_snippet']
            || window.DESKPRO_PERSON_PERMS['agent_snippets.create_self_snippet']) ?
              <ListElement>
                <Radio
                  id="ownership-me"
                  name="ownership"
                  value="me"
                  onChange={this.onRadioChange}
                  checked={radio === 'me'}
                >
                  {agentPhrases.get('agent.general.just_me')}
                </Radio>
              </ListElement>
            : null }
          { (window.DESKPRO_PERSON_PERMS['agent_snippets.create_snippet']
            || window.DESKPRO_PERSON_PERMS['agent_snippets.create_global_snippet']) ?
              <ListElement>
                <Radio
                  id="ownership-everyone"
                  name="ownership"
                  value="everyone"
                  onChange={this.onRadioChange}
                  checked={radio === 'everyone'}
                >
                  {agentPhrases.get('agent.general.everyone')}
                </Radio>
              </ListElement>
            : null }
          { (window.DESKPRO_PERSON_PERMS['agent_snippets.create_snippet']
            || window.DESKPRO_PERSON_PERMS['agent_snippets.create_team_snippet'])
          && (me.get('can_admin') || me.get('teams').size) ?
            <ListElement>
              <Radio
                id="ownership-specific"
                name="ownership"
                value="specific"
                onChange={this.onRadioChange}
                checked={radio === 'specific'}
              >
                {agentPhrases.get('agent.snippets.specific_teams')}
              </Radio>
            </ListElement>
            : null }
          { this.state.radio === 'specific' ? this.getSpecific() : null}
        </List>
      </CustomSelect>
    );
  }
}
