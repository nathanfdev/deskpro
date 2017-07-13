import React, { PropTypes } from 'react';
import classNames from 'classnames';
import Immutable from 'immutable';
import { Input, Checkbox, Radio } from 'deskpro-components/lib/Components/Forms';
import { List, ListElement } from 'deskpro-components/lib/Components/Common';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';

class SnippetsFiltering extends React.Component {
  static propTypes = {
    snippets:            PropTypes.object,
    labelFilter:         PropTypes.string,
    selectLabel:         PropTypes.func,
    selectMultiMode:     PropTypes.func,
    handleLabelFilter:   PropTypes.func,
    onMultiLabelsChange: PropTypes.func,
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
      labels: [],
    };
  }

  componentWillMount() {
    if (this.props.snippets) {
      this.extractLabels(this.props.snippets);
    }
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.snippets) {
      this.extractLabels(nextProps.snippets);
    }
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
        if (this.state.labelFilter === '') {
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
    const { labelFilter, handleLabelFilter, multiLabels } = this.props;
    return (
      <div className={classNames('snippets__labels', { 'multi-labels': multiLabels.length })}>
        <div className="title">
          <i className="fa fa-tag" />&nbsp;
          {agentPhrases.get('agent.general.labels')}
        </div>
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
    );
  }
}
export default SnippetsFiltering;
