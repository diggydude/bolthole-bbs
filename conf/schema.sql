SET FOREIGN_KEY_CHECKS = 0;
DROP DATABASE IF EXISTS `bolthole`;
CREATE DATABASE `bolthole`;
SET FOREIGN_KEY_CHECKS = 1;
USE `bolthole`;

/* *************** USERS *************** */

CREATE TABLE `User` (
  `id`          INT(11)      AUTO_INCREMENT,
  `username`    VARCHAR(32)  NOT NULL,
  `password`    VARCHAR(255) NOT NULL,
  `question`    VARCHAR(255) NOT NULL,
  `answer`      VARCHAR(255) NOT NULL,
  `joined`      TIMESTAMP    NOT NULL DEFAULT NOW(),
  `accessLevel` TINYINT(4)   NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE (`username`),
  CHECK (CHAR_LENGTH(`username`) > 3),
  CHECK (CHAR_LENGTH(`password`) > 7)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE `Profile` (
  `userId`      INT(11)      NOT NULL,
  `title`       VARCHAR(32)  NOT NULL DEFAULT '',
  `displayName` VARCHAR(32)  NOT NULL DEFAULT '',
  `avatar`      VARCHAR(32)  NOT NULL DEFAULT '',
  `signature`   VARCHAR(255) NOT NULL DEFAULT '',
  `website`     VARCHAR(128) NOT NULL DEFAULT '',
  `about`       TEXT         NOT NULL,
  `rendered`    TEXT         NOT NULL,
  PRIMARY KEY (`userId`),
  FOREIGN KEY (`userId`) REFERENCES `User` (`id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `Following` (
  `followerId` INT(11) NOT NULL,
  `followedId` INT(11) NOT NULL,
  UNIQUE (`followerId`, `followedId`),
  FOREIGN KEY (`followerId`) REFERENCES `User` (`id`),
  FOREIGN KEY (`followedId`) REFERENCES `User` (`id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE `WhosOnline` (
  `userId`    INT(11)     NOT NULL,
  `sessionId` VARCHAR(48) NOT NULL,
  `arrivedAt` TIMESTAMP   NOT NULL DEFAULT NOW(),
  UNIQUE (`userId`),
  UNIQUE (`sessionId`),
  FOREIGN KEY (`userId`) REFERENCES `User` (`id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE `Ban` (
  `userId`   INT(11)      NOT NULL,
  `bannedAt` TIMESTAMP    NOT NULL DEFAULT NOW(),
  `bannedBy` INT(11)      NOT NULL,
  `reason`   VARCHAR(128) NOT NULL,
  `expires`  TIMESTAMP    NOT NULL DEFAULT '0000-00-00 00:00:00',
  UNIQUE (`userId`),
  FOREIGN KEY (`userId`)   REFERENCES `User` (`id`),
  FOREIGN KEY (`bannedBy`) REFERENCES `User` (`id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

/* ****************** GLUE ****************** */

CREATE TABLE `Theme` (
  `id`   TINYINT(3)   NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE (`name`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

INSERT INTO `Theme` (`id`, `name`) VALUES (1, 'Citrus');
INSERT INTO `Theme` (`id`, `name`) VALUES (2, 'Seasick');
INSERT INTO `Theme` (`id`, `name`) VALUES (3, 'Stratosphere');
INSERT INTO `Theme` (`id`, `name`) VALUES (4, 'Twilight');

CREATE TABLE `ModuleType` (
  `id`   INT(11)      AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE (`name`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

INSERT INTO `ModuleType` (`id`, `name`) VALUES (1 , 'Profile');
INSERT INTO `ModuleType` (`id`, `name`) VALUES (2 , 'BlogPost');
INSERT INTO `ModuleType` (`id`, `name`) VALUES (3 , 'File');
INSERT INTO `ModuleType` (`id`, `name`) VALUES (4 , 'Store');

CREATE TABLE `Emoticon` (
  `code`     VARCHAR(32)  NOT NULL,
  `filename` VARCHAR(128) NOT NULL,
  UNIQUE (`code`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

INSERT INTO `Emoticon` (`code`, `filename`) VALUES (':blush:',  'blush.gif');
INSERT INTO `Emoticon` (`code`, `filename`) VALUES (':flirt:',  'flirt.gif');
INSERT INTO `Emoticon` (`code`, `filename`) VALUES (':grin:',   'grin.png');
INSERT INTO `Emoticon` (`code`, `filename`) VALUES (':lol:',    'lol.gif');
INSERT INTO `Emoticon` (`code`, `filename`) VALUES (':mad:',    'mad.gif');
INSERT INTO `Emoticon` (`code`, `filename`) VALUES (':sad:',    'sad.gif');
INSERT INTO `Emoticon` (`code`, `filename`) VALUES (':smile:',  'smile.png');
INSERT INTO `Emoticon` (`code`, `filename`) VALUES (':tongue:', 'tongue.png');
INSERT INTO `Emoticon` (`code`, `filename`) VALUES (':wave:',   'wave.gif');
INSERT INTO `Emoticon` (`code`, `filename`) VALUES (':wtf:',    'wtf.gif');

/* *************** FORUM *************** */

CREATE TABLE `ForumPost` (
  `id`        INT(11)      AUTO_INCREMENT,
  `postedBy`  INT(11)      NOT NULL,
  `postedAt`  TIMESTAMP    NOT NULL DEFAULT NOW(),
  `topic`     VARCHAR(255) NOT NULL,
  `body`      TEXT         NOT NULL,
  `rendered`  TEXT         NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`postedBy`) REFERENCES `User` (`id`),
  CHECK (CHAR_LENGTH(`topic`) > 3),
  CHECK (CHAR_LENGTH(`body`)  > 9)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE `ForumThread` (
  `id`         INT(11)      AUTO_INCREMENT,
  `topic`      VARCHAR(255) NOT NULL,
  `startedAt`  TIMESTAMP    NOT NULL DEFAULT NOW(),
  `startedBy`  INT(11)      NOT NULL,
  `lastPostId` INT(11)      NOT NULL,
  `lastPostAt` TIMESTAMP    NOT NULL DEFAULT NOW(),
  `lastPostBy` INT(11)      NOT NULL,
  `locked`     TINYINT(1)   NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`startedBy`)  REFERENCES `User`      (`id`),
  FOREIGN KEY (`lastPostId`) REFERENCES `ForumPost` (`id`),
  FOREIGN KEY (`lastPostBy`) REFERENCES `User`      (`id`),
  CHECK (CHAR_LENGTH(`topic`) > 3),
  CHECK (`locked` IN ('0', '1'))
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE `ForumPostReply` (
  `postId`    INT(11) NOT NULL,
  `inReplyTo` INT(11) NOT NULL,
  UNIQUE(`postId`, `inReplyTo`),
  FOREIGN KEY (`postId`)    REFERENCES `ForumPost` (`id`),
  FOREIGN KEY (`inReplyTo`) REFERENCES `ForumPost` (`id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE `ForumPostInThread` (
  `postId`   INT(11) NOT NULL,
  `threadId` INT(11) NOT NULL,
  UNIQUE (`postId`, `threadId`),
  FOREIGN KEY (`postId`)   REFERENCES `ForumPost`   (`id`),
  FOREIGN KEY (`threadId`) REFERENCES `ForumThread` (`id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

/* *************** CHAT & MESSAGING *************** */

CREATE TABLE `Comment` (
  `id`           INT(11)   AUTO_INCREMENT,
  `moduleTypeId` INT(11)   NOT NULL,
  `moduleId`     INT(11)   NOT NULL,
  `postedBy`     INT(11)   NOT NULL,
  `postedAt`     TIMESTAMP NOT NULL DEFAULT NOW(),
  `body`         TEXT      NOT NULL,
  `rendered`     TEXT      NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`moduleTypeId`) REFERENCES `ModuleType` (`id`),
  FOREIGN KEY (`postedBy`)     REFERENCES `User`       (`id`),
  CHECK (CHAR_LENGTH(`body`) > 3)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE `Chat` (
  `id`       INT(11)   AUTO_INCREMENT,
  `postedBy` INT(11)   NOT NULL DEFAULT '1',
  `postedAt` TIMESTAMP NOT NULL DEFAULT NOW(),
  `body`     TEXT      NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`postedBy`) REFERENCES `User` (`id`),
  CHECK (CHAR_LENGTH(`body`) > 3)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE `Mail` (
  `id`        INT(11)      AUTO_INCREMENT,
  `to`        INT(11)      NOT NULL,
  `from`      INT(11)      NOT NULL,
  `postedAt`  TIMESTAMP    NOT NULL DEFAULT NOW(),
  `subject`   VARCHAR(255) NOT NULL DEFAULT 'Untitled Message',
  `body`      TEXT         NOT NULL,
  `rendered`  TEXT         NOT NULL,
  `delivered` TINYINT(1)   NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`to`)   REFERENCES `User` (`id`),
  FOREIGN KEY (`from`) REFERENCES `User` (`id`),
  CHECK (CHAR_LENGTH(`body`) > 3),
  CHECK (`delivered` IN ('0', '1'))
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE `EventType` (
  `id`   INT(11)     AUTO_INCREMENT,
  `type` VARCHAR(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE (`type`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

INSERT INTO `EventType` (`id`, `type`) VALUES (1,  'generic');
INSERT INTO `EventType` (`id`, `type`) VALUES (2,  'notifyReply');
INSERT INTO `EventType` (`id`, `type`) VALUES (3,  'notifyVisit');
INSERT INTO `EventType` (`id`, `type`) VALUES (4,  'notifyComment');
INSERT INTO `EventType` (`id`, `type`) VALUES (5,  'notifyMention');
INSERT INTO `EventType` (`id`, `type`) VALUES (6,  'notifyDownload');
INSERT INTO `EventType` (`id`, `type`) VALUES (7,  'notifyBkmkProfile');
INSERT INTO `EventType` (`id`, `type`) VALUES (8,  'notifyBkmkBlogPost');
INSERT INTO `EventType` (`id`, `type`) VALUES (9,  'notifyBkmkUpload');
INSERT INTO `EventType` (`id`, `type`) VALUES (10, 'notifyAnyProfile');
INSERT INTO `EventType` (`id`, `type`) VALUES (11, 'notifyAnyBlogPost');
INSERT INTO `EventType` (`id`, `type`) VALUES (12, 'notifyAnyUpload');
INSERT INTO `EventType` (`id`, `type`) VALUES (13, 'notifyUserSignup');
INSERT INTO `EventType` (`id`, `type`) VALUES (14, 'notifyUserBanned');

CREATE TABLE `Event` (
  `id`          INT(11)    AUTO_INCREMENT,
  `typeId`      INT(11)    NOT NULL,
  `recipientId` INT(11)    NOT NULL DEFAULT '1',
  `occurredAt`  TIMESTAMP  NOT NULL DEFAULT NOW(),
  `private`     TINYINT(1) NOT NULL DEFAULT '0',
  `data`        TEXT       NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`typeId`)      REFERENCES `EventType` (`id`),
  FOREIGN KEY (`recipientId`) REFERENCES `User`      (`id`),
  CHECK (`private`   IN ('0', '1'))
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE `EventDispatch` (
  `eventId`     INT(11) NOT NULL,
  `recipientId` INT(11) NOT NULL,
  UNIQUE (`eventId`, `recipientId`),
  FOREIGN KEY (`eventId`)     REFERENCES `Event` (`id`),
  FOREIGN KEY (`recipientId`) REFERENCES `User`  (`id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;


/* *************** BLOGS *************** */

CREATE TABLE `Blog` (
  `id`      INT(11) AUTO_INCREMENT,
  `ownerId` INT(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`ownerId`) REFERENCES `User` (`id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE `BlogPost` (
  `id`       INT(11)      AUTO_INCREMENT,
  `inBlog`   INT(11)      NOT NULL,
  `title`    VARCHAR(128) NOT NULL,
  `postedAt` TIMESTAMP    NOT NULL DEFAULT NOW(),
  `editedAt` TIMESTAMP    NOT NULL DEFAULT '0000-00-00 00:00:00',
  `body`     TEXT         NOT NULL,
  `rendered` TEXT         NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`inBlog`) REFERENCES `Blog` (`id`),
  CHECK (CHAR_LENGTH(`body`) > 9)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

/* *************** USER FILES *************** */

CREATE TABLE `File` (
  `id`       INT(11)      AUTO_INCREMENT,
  `mimeType` VARCHAR(128) NOT NULL,
  `size`     INT(11)      NOT NULL,
  `hash`     VARCHAR(32)  NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE (`hash`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE `Library` (
  `id`      INT(11) AUTO_INCREMENT,
  `ownerId` INT(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`ownerId`) REFERENCES `User` (`id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE `FileInLibrary` (
  `id`          INT(11)      AUTO_INCREMENT,
  `fileId`      INT(11)      NOT NULL,
  `libraryId`   INT(11)      NOT NULL,
  `filename`    VARCHAR(128) NOT NULL,
  `description` TEXT         NOT NULL,
  `rendered`    TEXT         NOT NULL,
  `uploadedAt`  TIMESTAMP    NOT NULL DEFAULT NOW(),
  `downloads`   INT(11)      NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE (`fileId`, `libraryId`),
  FOREIGN KEY (`fileId`)    REFERENCES `File`    (`id`),
  FOREIGN KEY (`libraryId`) REFERENCES `Library` (`id`),
  CHECK (CHAR_LENGTH(`filename`) > 3),
  CHECK (CHAR_LENGTH(`description`) > 23)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

/* **************** CHANNELS **************** */

CREATE TABLE `Channel` (
  `id`          INT(11)      AUTO_INCREMENT,
  `title`       VARCHAR(255) NOT NULL,
  `uri`         VARCHAR(255) NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `keywords`    VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE (`uri`),
  CHECK (CHAR_LENGTH(`uri`) > 9),
  CHECK (CHAR_LENGTH(`description`) > 23)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

/* ***************** STORES ***************** */

CREATE TABLE `Store` (
  `id`            INT(11)      AUTO_INCREMENT,
  `name`          VARCHAR(255) NOT NULL,
  `ownerId`       INT(11)      NOT NULL,
  `paypalAddress` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE (`name`),
  FOREIGN KEY (`ownerId`) REFERENCES `User` (`id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE `Product` (
  `id`          INT(11)      AUTO_INCREMENT,
  `storeId`     INT(11)      NOT NULL,
  `itemCode`    VARCHAR(32)  NOT NULL,
  `name`        VARCHAR(128) NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `photo1`      INT(11)      DEFAULT '0',
  `photo2`      INT(11)      DEFAULT '0',
  `photo3`      INT(11)      DEFAULT '0',
  `price`       FLOAT        NOT NULL,
  `listedAt`    TIMESTAMP    NOT NULL DEFAULT NOW(),
  `editedAt`    TIMESTAMP    NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sold`        TINYINT(1)   NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`storeId`) REFERENCES `Store` (`id`),
  CHECK (`sold` IN (0, 1))
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE `Customer` (
  `userId`             INT(11)      NOT NULL,
  `name`               VARCHAR(255) NOT NULL,
  `paypalAddress`      VARCHAR(255) NOT NULL,
  `billingAddress1`    VARCHAR(128) NOT NULL,
  `billingAddress2`    VARCHAR(128) NOT NULL,
  `billingCity`        VARCHAR(128) NOT NULL,
  `billingState`       VARCHAR(2)   NOT NULL,
  `billingCountry`     VARCHAR(2)   NOT NULL,
  `billingPostalCode`  VARCHAR(10)  NOT NULL,
  `shippingAddress1`   VARCHAR(128) NOT NULL,
  `shippingAddress2`   VARCHAR(128) NOT NULL,
  `shippingCity`       VARCHAR(128) NOT NULL,
  `shippingState`      VARCHAR(2)   NOT NULL,
  `shippingCountry`    VARCHAR(2)   NOT NULL,
  `shippingPostalCode` VARCHAR(10)  NOT NULL,
  PRIMARY KEY (`userId`),
  FOREIGN KEY (`userId`) REFERENCES `User` (`id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE `Invoice` (
  `id`             INT(11)   AUTO_INCREMENT,
  `customerId`     INT(11)   NOT NULL,
  `storeId`        INT(11)   NOT NULL,
  `subtotal`       FLOAT     NOT NULL,
  `transactionFee` FLOAT     NOT NULL,
  `total`          FLOAT     NOT NULL,
  `orderedAt`      TIMESTAMP NOT NULL DEFAULT NOW(),
  `paidAt`         TIMESTAMP NOT NULL DEFAULT '000-00-00 00:00:00',
  `cancelledAt`    TIMESTAMP NOT NULL DEFAULT '000-00-00 00:00:00',
  `shippedAt`      TIMESTAMP NOT NULL DEFAULT '000-00-00 00:00:00',
  `trackingNumber` VARCHAR(255),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`customerId`) REFERENCES `Customer` (`userId`),
  FOREIGN KEY (`storeId`)    REFERENCES `Store`    (`id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE `ProductInInvoice` (
  `productId` INT(11) NOT NULL,
  `invoiceId` INT(11) NOT NULL,
  `price`     FLOAT   NOT NULL,
  FOREIGN KEY (`productId`) REFERENCES `Product` (`id`),
  FOREIGN KEY (`invoiceId`) REFERENCES `Invoice` (`id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;