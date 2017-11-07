import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import { ListItemStatefulContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { urlSanitize } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Service/routing';

export class LabelsDictionary extends React.Component {

  static propTypes = {
    onClick: PropTypes.func.isRequired,
    labels:  PropTypes.object.isRequired
  };

  groupByFirstLetter() {
    const { labels } = this.props;
    const dictionary = {};
    let count;

    // count BC both for Immutable and JS objects
    count = labels.count() ? labels.count() : labels.size;
    for (let index = 0, label, letter; index < count; index++) {
      label = Immutable.Iterable.isIterable(labels) ? labels.get(index) : labels[index];
      letter = label.get('label')[0];
      if (!letter) {
        continue;
      }

      letter = letter.toUpperCase();
      if (!dictionary.hasOwnProperty(letter)) {
        dictionary[letter] = [];
      }

      dictionary[letter].push(label);
    }

    const letters = Object.keys(dictionary);
    const grouped = [];

    // count BC both for Immutable and JS objects
    count = letters.count ? letters.count() : letters.length;
    for (let index = 0; index < count; index++) {
      grouped.push({ letter: letters[index], labels: dictionary[letters[index]] });
    }

    return grouped;
  }

  renderLabel = (label, key) => {
    const { onClick } = this.props;
    const labelName = label.get('label');

    return (
      <ListItemContainer onClick={onClick} label={labelName}>
        <li key={key}>
          <a href="#" className="item-label" style={{ color: label.get('color') }}>
            {label.get('label')}
          </a>
        </li>
      </ListItemContainer>
    );
  };

  render() {
    const { onClick } = this.props;

    return (
      <section className="sidebar-list sidebar-list-labels tasks-nav-labels">
        <div className="sidebar-label-list sidebar-list">
          <span className="labelCharacter">--</span>
          <ul>
            <li onClick={() => onClick({ name: 'no_labels', value: 1 })}>
              <a href="#" className="item-label">no labels defined</a>
            </li>
          </ul>
          {this.groupByFirstLetter().map(
            (group, index) =>
              <div key={index}>
                <span className="labelCharacter">{group.letter}</span>
                <ul>
                  {group.labels.map((label, key) => this.renderLabel(label, key))}
                </ul>
              </div>
          )}
        </div>
      </section>
    );
  }
}

@connect(state => ({
  hash: state.Application.routing.get('hash')
}))
export class ListItemContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    onClick:  PropTypes.func.isRequired,
    label:    PropTypes.string.isRequired,
    children: PropTypes.node.isRequired,
    hash:     PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.itemId = urlSanitize(props.label);
  }

  componentDidMount = () => {
    const { hash, dispatch, label, onClick } = this.props;
    const activeItemId = hash.get('nav') ? hash.get('nav').get('label') : null;

    if (activeItemId === urlSanitize(label)) {
      dispatch(onClick({ label: [label] }));
    }
  };

  loadList = () => {
    const { dispatch, onClick, label } = this.props;
    dispatch(onClick({ label: [label] }));
  };

  render() {
    const { label, children } = this.props;
    const props = {
      label, children,

      groupId: 'nav',
      active:  'label',
      itemId:  this.itemId,
      onClick: this.loadList
    };

    return <ListItemStatefulContainer {...props} />;
  }
}
