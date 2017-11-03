import React, { PropTypes } from 'react';
import classNames from 'classnames';
import Immutable from 'immutable';
import Isvg from 'react-inlinesvg';
import { Input, Checkbox, Radio } from '@deskpro/react-components/lib/Components/Forms';
import { List, ListElement } from '@deskpro/react-components/lib/Components/Common';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';

class SnippetsFiltering extends React.Component {
  static propTypes = {
    me:                  PropTypes.object,
    snippets:            PropTypes.object,
    filteredSnippets:    PropTypes.object,
    labelFilter:         PropTypes.string,
    clearLabels:         PropTypes.func,
    selectLabel:         PropTypes.func,
    selectMultiMode:     PropTypes.func,
    handleLabelFilter:   PropTypes.func,
    onMultiLabelsChange: PropTypes.func,
    handleShowMode:      PropTypes.func,
    height:              PropTypes.number,
    showMode:            PropTypes.string,
    selectedLabel:       PropTypes.string,
    multiMode:           PropTypes.string,
    multiLabels:         PropTypes.array,
  };
  static defaultProps = {
    labelFilter: '',
    multiLabels: [],
  };

  constructor(props) {
    super(props);
    this.state = {
      labels:      [],
      showOptions: [],
    };
  }

  componentWillMount() {
    if (this.props.snippets) {
      this.extractLabels(this.props.snippets);
    }
    if (this.props.filteredSnippets) {
      this.countShowOptions(this.props.filteredSnippets);
    }
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.snippets !== this.props.snippets) {
      this.extractLabels(nextProps.snippets);
    }
    if (nextProps.filteredSnippets !== this.props.filteredSnippets) {
      this.countShowOptions(nextProps.filteredSnippets);
    }
  }

  shouldComponentUpdate(nextProps) {
    if (nextProps.snippets !== this.props.snippets) {
      return true;
    }
    return nextProps.selectedLabels !== this.props.selectedLabel;
  }

  getChildren = (label) => {
    const labels = label.get('children');
    if (!labels.size) {
      return null;
    }
    return <List>{this.getLabels(labels)}</List>;
  };

  getLabels = (labels) => {
    const result = [];

    function findInChildren(value, re) {
      if (value.get('children').find(e => e.get('tag').match(re))) {
        return true;
      }
      let found = false;
      value.get('children').forEach((child) => {
        if (child.get('children').size) {
          found = findInChildren(child, re);
          if (found) {
            return false;
          }
        }
        return true;
      });
      return found;
    }

    labels
      .filter((value) => {
        const { labelFilter } = this.props;
        if (labelFilter === '') {
          return true;
        }
        const re = new RegExp(labelFilter, 'i');
        return value.get('tag').match(re) || findInChildren(value, re);
      })
      .sort((a, b) => {
        const atag = a.get('tag').toLowerCase();
        const btag = b.get('tag').toLowerCase();
        if (atag > btag) {
          return 1;
        } else if (atag < btag) {
          return -1;
        }
        return 0;
      })
      .forEach((label, key) => {
        result.push(
          <ListElement
            key={key}
            className={classNames({ selected: label.get('tag') === this.props.selectedLabel })}
          >
            <div className="element">
              <Checkbox
                value={label.get('tag')}
                checked={this.props.multiLabels.indexOf(label.get('tag')) !== -1}
                onChange={this.props.onMultiLabelsChange}
                stopPropagation
              />
              <span className="tag" onClick={e => this.props.selectLabel(e, label.get('tag'))}>
                {label.get('label')}
              </span>
              &nbsp;
              <span className="count" onClick={e => this.props.selectLabel(e, label.get('tag'))}>
                ({label.get('snippets').size})
              </span>
            </div>
            {this.getChildren(label)}
          </ListElement>
      );
      });
    return result;
  };

  extractLabels = (snippets) => {
    const occurrences = {};
    if (!snippets) {
      this.setState({
        labels: []
      });
    }
    snippets.forEach((snippet) => {
      if (snippet.get('labels')) {
        snippet.get('labels').forEach((label) => {
          const parts = label.split('/');
          this.insertOccurrence(parts[0].trim(), parts[0].trim(), occurrences, parts.slice(1), snippet.get('id'));
        });
      }
    });
    this.setState({
      labels: Immutable.fromJS(occurrences)
    });
  };

  countShowOptions = (snippets) => {
    const { me } = this.props;
    const allSnippets = snippets.count(snippet => !snippet.get('is_draft', false));
    const showOptions = [
      {
        value: 'all',
        label: agentPhrases.get('agent.snippets.all_snippets'),
        count: allSnippets,
      },
    ];
    const myId = me.get('id');
    const mySnippets = snippets.count(snippet =>
      snippet.get('person') === myId && !snippet.get('is_draft', false)
    );
    showOptions.push({
      value: 'my_snippets',
      label: agentPhrases.get('agent.snippets.my_snippets'),
      count: mySnippets,
    });
    const myTeams = me.get('teams', new Immutable.List());
    if (myTeams.size > 0) {
      const teamSnippets = snippets.count(snippet =>
        !snippet.get('is_draft', false) &&
        snippet.get('ownership_teams', new Immutable.List())
          .filter(team => myTeams.find(t => t === team)).size > 0
      );
      showOptions.push({
        value: 'my_team',
        label: me.get('teams').size > 1 ?
                 agentPhrases.get('agent.snippets.my_teams_snippets')
                 : agentPhrases.get('agent.snippets.my_team_snippets'),
        count: teamSnippets,
      });
    }
    const myDrafts = snippets.count(snippet =>
      snippet.get('is_draft', false) && snippet.get('person') === me.get('id')
    );
    showOptions.push({
      value: 'my_drafts',
      label: agentPhrases.get('agent.snippets.my_drafts'),
      count: myDrafts,
    });
    const allDrafts = snippets.count(snippet => snippet.get('is_draft', false));
    showOptions.push({
      value: 'all_drafts',
      label: agentPhrases.get('agent.snippets.all_drafts'),
      count: allDrafts,
    });
    this.setState({
      showOptions
    });
  };

  insertOccurrence(tag, label, occurrences, parts, snippetId) {
    if (occurrences[label]) {
      if (occurrences[label].snippets.indexOf(snippetId) === -1) {
        occurrences[label].snippets.push(snippetId);
      }
    } else {
      occurrences[label] = {
        label,
        tag,
        children: {},
        snippets: [snippetId]
      };
    }
    if (parts.length > 0) {
      this.insertOccurrence(
        `${tag}/${parts[0].trim()}`,
        parts[0].trim(),
        occurrences[label].children,
        parts.slice(1),
        snippetId
      );
    }
  }

  render() {
    const { labelFilter, handleLabelFilter, multiLabels, selectedLabel } = this.props;
    let height = this.props.height - 123;
    if (isNaN(height)) {
      height = 400;
    }
    return (
      <div
        className={classNames('snippets__filtering', { 'multi-labels': multiLabels.length })}
        style={{ maxHeight: height }}
      >
        <div className="show block">
          <div className="title">
            {agentPhrases.get('agent.general.show')}
          </div>
          {this.state.showOptions.map(option =>
            <Radio
              key={option.value}
              onChange={this.props.handleShowMode}
              checked={option.value === this.props.showMode}
              value={option.value}
            >
              <span className="label">{option.label}</span> ({option.count})
            </Radio>
          )}
        </div>
        <div className="labels block">
          <div className="title">
            <i className="fa fa-tag" />&nbsp;
            {agentPhrases.get('agent.general.labels')}
          </div>
          { multiLabels.length || selectedLabel ?
            <a className="clear" onClick={this.props.clearLabels}>
              <Isvg
                className="close-icon"
                src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/general/close.svg`}
              />
              {agentPhrases.get('agent.general.clear')}
            </a>
              : null
            }
          <Input
            value={labelFilter}
            className="search"
            onChange={handleLabelFilter}
            icon="search"
          />
          { multiLabels.length ?
            <div className="multi-mode">
              <Radio
                checked={this.props.multiMode === 'any'}
                onChange={this.props.selectMultiMode}
                value="any"
                name="label-mode"
              >
                  Match any
                </Radio>
              <Radio
                checked={this.props.multiMode === 'all'}
                onChange={this.props.selectMultiMode}
                value="all"
                name="label-mode"
              >
                  Match all
                </Radio>
            </div>
              : null}
          <List>
            {this.getLabels(this.state.labels)}
          </List>
        </div>
      </div>
    );
  }
}
export default SnippetsFiltering;
