import React, { PropTypes } from 'react';
import { ImageAvatar, Gravatar, TextAvatar } from 'DeskPRO/Component/Avatar';
import { UserPhoto } from './UserPhoto';

export class Avatar extends React.Component {

  static propTypes = {
    size:       PropTypes.number,
    url:        PropTypes.string,
    urlPattern: PropTypes.string,
    gravatar:   PropTypes.string,
    classes:    PropTypes.array,
  };

  static defaultProps = {
    classes: []
  };

  render() {
    const { url, urlPattern, gravatar, size, classes } = this.props;

    if (url || urlPattern) {
      return (
        <ImageAvatar {...this.props}>
          <UserPhoto classes={classes} />
        </ImageAvatar>
      );
    }
    if (gravatar) {
      return (
        <TextAvatar {...this.props}>
          <UserPhoto type="text" classes={classes}>
            <Gravatar gravatar={gravatar} size={size}>
              <UserPhoto type="gravatar" classes={classes}/>
            </Gravatar>
          </UserPhoto>
        </TextAvatar>
      );
    }

    return (
      <TextAvatar {...this.props}>
        <UserPhoto type="text"classes={classes}  />
      </TextAvatar>
    );
  }
}
